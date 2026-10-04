<?php

declare(strict_types=1);

namespace OCA\ThreemfPreview\Preview;

use OCP\Files\File;
use OCP\Files\FileInfo;
use OCP\IImage;
use OCP\Image;
use OCP\ITempManager;
use OCP\Preview\IProviderV2;
use OCP\Server;

/**
 * Preview provider for .3mf files: a 3MF is an OPC (zip) package whose
 * _rels/.rels points at an embedded thumbnail image.
 */
class ThreeMF implements IProviderV2 {
	private const THUMB_REL = 'http://schemas.openxmlformats.org/package/2006/relationships/metadata/thumbnail';
	private const FALLBACKS = [
		'Auxiliaries/.thumbnails/thumbnail_3mf.png',
		'Metadata/plate_1.png',
		'Thumbnails/thumbnail.png',
	];

	public function getMimeType(): string {
		return '/model\/3mf/';
	}

	public function isAvailable(FileInfo $file): bool {
		return $file->getSize() > 0 && class_exists(\ZipArchive::class);
	}

	public function getThumbnail(File $file, int $maxX, int $maxY): ?IImage {
		$tmp = Server::get(ITempManager::class)->getTemporaryFile('.3mf');
		try {
			$in = $file->fopen('r');
			if ($in === false) {
				return null;
			}
			file_put_contents($tmp, $in);
			fclose($in);

			$zip = new \ZipArchive();
			if ($zip->open($tmp) !== true) {
				return null;
			}
			try {
				foreach ($this->candidatePaths($zip) as $path) {
					$data = $zip->getFromName($path);
					if ($data === false || $data === '') {
						continue;
					}
					$image = new Image();
					if ($image->loadFromData($data) !== false && $image->valid()) {
						$image->fixOrientation();
						return $image;
					}
				}
			} finally {
				$zip->close();
			}
		} catch (\Throwable $e) {
			return null;
		} finally {
			@unlink($tmp);
		}
		return null;
	}

	/** @return string[] zip entry names to try, best first */
	private function candidatePaths(\ZipArchive $zip): array {
		$paths = [];
		$rels = $zip->getFromName('_rels/.rels');
		if ($rels !== false) {
			$prev = libxml_use_internal_errors(true);
			$xml = simplexml_load_string($rels, 'SimpleXMLElement', LIBXML_NONET);
			libxml_use_internal_errors($prev);
			if ($xml !== false) {
				foreach ($xml->Relationship as $rel) {
					if ((string)$rel['Type'] === self::THUMB_REL) {
						$paths[] = ltrim((string)$rel['Target'], '/');
					}
				}
			}
		}
		return array_values(array_unique(array_merge($paths, self::FALLBACKS)));
	}
}

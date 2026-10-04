<?php

declare(strict_types=1);

namespace OCA\ThreemfPreview\AppInfo;

use OCA\ThreemfPreview\Preview\ThreeMF;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;

class Application extends App implements IBootstrap {
	public const APP_ID = 'threemfpreview';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerPreviewProvider(ThreeMF::class, '/model\/3mf/');
	}

	public function boot(IBootContext $context): void {
	}
}

# threemfpreview

Nextcloud app that shows the thumbnail embedded in `.3mf` files (Bambu Studio,
PrusaSlicer, Cura, ...) as the file preview in the web UI, mobile apps and the
Windows virtual-files client.

A 3MF is a zip package. This app registers a preview provider for `model/3mf`
that reads `_rels/.rels`, finds the standard `metadata/thumbnail` relationship
and returns that image. If the relationship is missing it falls back to
`Auxiliaries/.thumbnails/thumbnail_3mf.png`, `Metadata/plate_1.png` and
`Thumbnails/thumbnail.png`. Nothing is rendered: files without an embedded
thumbnail get no preview.

Uses only the public `OCP\` API. Tested on Nextcloud 34 with S3 primary storage.

## Requirements

- Nextcloud 30 - 34 (adjust `max-version` in `appinfo/info.xml` for newer releases)
- PHP `zip` extension (`ZipArchive`)
- The `3mf` extension mapped to `model/3mf`. Nextcloud's default
  `mimetypemapping.json` does not include it; add to
  `config/mimetypemapping.json`:

  ```json
  { "3mf": ["model/3mf"] }
  ```

  then run `occ maintenance:mimetype:update-db`.

## Install

Copy this directory to `custom_apps/threemfpreview` (owner `www-data`), then:

```
occ app:enable threemfpreview
```

Previews are generated on demand. To pre-generate them for existing files use
the Preview Generator app.

## Windows virtual-files client

When Nextcloud's virtual files are enabled, Windows registers the sync folder as
a sync root with its own thumbnail provider, which bypasses any local
`.3mf` thumbnail handler and asks the server instead. This app is what makes
that request succeed. If thumbnails stay blank after installing, clear
Explorer's cache (`%LOCALAPPDATA%\Microsoft\Windows\Explorer\thumbcache_*.db`)
and restart the Nextcloud client.

## Uninstall

```
occ app:disable threemfpreview
```

## License

AGPL-3.0-or-later. See `LICENSE`.

# Installation guide

## Requirements

- PHP 8.3 or later with the `fileinfo` extension.
- Yii2 `2.0.55` or later, or the Yii2 `22.x` development line.
- Composer `2.10.2` or later.
- [`php-forge/foxy`](https://github.com/php-forge/foxy) `^0.4` in the consuming application, with PHP `zlib` for its
  native manager. Node.js and a JavaScript package manager are optional for installing this extension's assets.
- The `gd` extension when images are cropped on the server with `ImageCropper`.

Yii2 `2.0.x` also needs its Bower asset packages. If the application's root `composer.json` does not already configure
an asset repository, add Asset Packagist:

```json
{
  "repositories": [{ "type": "composer", "url": "https://asset-packagist.org" }]
}
```

This setting belongs to the application root; Composer does not load repository settings from dependencies.

## Install the extension

For a PHP application, use Foxy's native manager to download the published FilePond, plugin, and Cropper.js assets
through Composer. Merge this setting into the application's root `composer.json` before requiring the packages:

```json
{
  "config": {
    "foxy": {
      "manager": "native"
    }
  }
}
```

Authorize and require Foxy in that application:

```bash
composer config allow-plugins.php-forge/foxy true
composer require php-forge/foxy:^0.4 yii2-extensions/filepond:^0.2
```

The extension declares `extra.foxy=true`, so Foxy reads its runtime frontend requirements, merges them into the
application `package.json`, and installs them in `node_modules` beside that file. FilePond's published assets and the
extension's own committed assets are ready to serve. The extension's JavaScript development dependencies and build
scripts are not copied into the application. Configuring Foxy inside the installed extension does not select the
application's manager; the application owns that setting and must require and authorize the plugin itself.

Commit the application's `composer.json`, `composer.lock`, `package.json`, and generated `foxy.lock`. `composer install`
reuses matching `foxy.lock` versions, while `composer update` resolves frontend versions again. Do not commit the
generated install directory. The native manager verifies registry tarball integrity, installs a flat dependency graph,
and does not execute lifecycle scripts or provide executable links. See
[Foxy's native manager documentation](https://github.com/php-forge/foxy/blob/0.4.0/docs/config.md#native-manager) for
its supported package specifications and constraints.

### Applications with an existing frontend build

Keep the application's JavaScript manager when its dependencies need installation scripts, executable tools, or nested
dependency versions. Foxy also supports npm, pnpm, Yarn, Bun, and Deno; set the chosen manager in the application's root
`composer.json`, for example `"manager": "npm"`, and retain that manager's lockfile.

The native manager owns its install directory and removes unlisted packages. Do not share that directory with npm or
another installer. To isolate native assets from an existing `node_modules`, configure a dedicated directory:

```json
{
  "config": {
    "foxy": {
      "manager": "native",
      "native-install-dir": "runtime/frontend-assets"
    }
  }
}
```

A relative `native-install-dir` is resolved from the directory containing the application's `package.json`
(`config.foxy.root-package-json-dir`, or the Composer root by default). Set `@npm` to the resulting directory. Native
mode installs the application's own frontend dependencies too; moving the install directory does not make build tools
that require Node.js compatible with native installation.

## Point the `@npm` alias to the asset directory

Yii2 `2.0.x` defaults to `@vendor/npm`; Yii2 `22.x` defaults to `@app/node_modules`. Verify the alias against the actual
Foxy install location in either version. For `config/web.php` one directory below the Composer root:

```php
// config/web.php
return [
    'aliases' => [
        '@npm' => dirname(__DIR__) . '/node_modules',
    ],
];
```

`@app/node_modules` is equivalent only when the application's `basePath` is the Composer root. For the dedicated
directory above, use `dirname(__DIR__) . '/runtime/frontend-assets'` instead. Adjust these paths when the configuration
file or frontend manifest lives elsewhere. The asset bundles publish each package's `dist` files from `@npm`.

## Register the bootstrap

The package declares `yii2\extensions\filepond\Bootstrap` in its Composer metadata, so applications that load
`@vendor/yiisoft/extensions.php` register the `yii.filepond` message source automatically. Applications that disable
extension loading register it explicitly:

```php
// config/web.php
return [
    'bootstrap' => [\yii2\extensions\filepond\Bootstrap::class],
];
```

The bootstrap never overrides a `yii.filepond` translation source configured by the application.

## Load the assets from a CDN

Set `cdn` on the widget to link FilePond, its plugins, and Cropper.js from `unpkg.com`, pinned to the versions installed
under `@npm`. The local package manifests are required even in CDN mode:

```php
echo \yii2\extensions\filepond\FilePond::widget(['name' => 'file', 'cdn' => true]);
```

For application-wide CDN settings and precedence, see [asset delivery](configuration.md#asset-delivery).

[Back to documentation](../README.md#documentation)

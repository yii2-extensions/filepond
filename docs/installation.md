# Installation guide

## Requirements

- PHP 8.3 or later with the `fileinfo` extension.
- Yii2 `2.0.54` or later, or the Yii2 `22.x` development line.
- Composer `2.10.2` or later.
- A frontend package manager supported by [`php-forge/foxy`](https://github.com/php-forge/foxy) (npm, pnpm, Yarn,
  Bun, or Deno) to install FilePond, its plugins, and Cropper.js.
- The `gd` extension when images are cropped on the server with `ImageCropper`.

## Install the extension

Authorize Foxy in the application, so Composer installs the frontend packages declared by the extension:

```bash
composer config allow-plugins.php-forge/foxy true
composer require php-forge/foxy:^0.3 yii2-extensions/filepond
```

Foxy merges the `filepond`, `filepond-plugin-*`, and `cropperjs` packages into the application `package.json` and
runs the selected manager, so the files end up in the application `node_modules` directory. Select the manager
explicitly for reproducible installs:

```json
{
    "config": {
        "foxy": {
            "manager": "npm"
        }
    }
}
```

## Point the `@npm` alias to `node_modules`

Yii2 `22.x` resolves `@npm` to the application `node_modules` directory by default. Yii2 `2.0.x` still points it to
`vendor/npm-asset`, so override the alias in the application configuration:

```php
// config/web.php
return [
    'aliases' => [
        '@npm' => '@app/node_modules',
    ],
];
```

The asset bundles publish the `dist` files of every package from that alias.

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

Set `cdn` on the widget to link FilePond, its plugins, and Cropper.js from `unpkg.com`, pinned to the versions
installed in `node_modules`:

```php
echo \yii2\extensions\filepond\FilePond::widget(['name' => 'file', 'cdn' => true]);
```

The delivery mode can also be fixed for the whole application through the asset manager:

```php
// config/web.php
return [
    'components' => [
        'assetManager' => [
            'bundles' => [
                \yii2\extensions\filepond\asset\FilePondAsset::class => [
                    'cdn' => true,
                    'cdnUrl' => 'https://cdn.jsdelivr.net/npm',
                ],
            ],
        ],
    ],
];
```

Bundles configured by the application keep their settings regardless of the widget `cdn` value.

## Next steps

- ⚙️ [Configuration Reference](configuration.md)
- 💡 [Usage Examples](examples.md)
- 🧪 [Testing Guide](testing.md)

# Configuration guide

`yii2\extensions\filepond\FilePond` extends `yii\widgets\InputWidget`, so `name`, `model`, `attribute`, and `options`
work as in any Yii2 input widget. Its typed properties map to the FilePond options of the same name; the
[API reference](https://yii2-framework.github.io/docs/extensions/filepond/reference/) lists every property with its
type, default value, and description, so this guide explains how the options work together.

## Widget options

```php
use yii2\extensions\filepond\FilePond;

echo FilePond::widget(
    [
        'name' => 'avatar',
        'acceptedFileTypes' => ['image/jpeg', 'image/png'],
        'maxFileSize' => '2MB',
        'config' => ['credits' => false],
    ],
);
```

Options are assembled in this order, each layer overriding the previous one:

1. Localized labels for the core and the enabled validation plugins.
2. Typed properties, omitting `null` values and empty arrays.
3. `config`, which accepts any FilePond option, including callbacks written as `yii\web\JsExpression`.

`FilePond::getOptions()` returns the merged result, the object passed to `FilePond.create()`.

## Plugins and assets

The `yii2\extensions\filepond\asset\Plugin` enum maps supported plugin options to their npm packages and asset bundles.
When a mapped option is `true` in the effective options, the widget registers the plugin with
`FilePond.registerPlugin()` and loads its bundle. Disabled plugins load nothing. Core options such as `allowMultiple`
do not register a plugin. A `config` override also affects this selection.

`allowImageEdit` additionally loads Cropper.js and the editor adapter. Keep `allowFileEncode` enabled for the
[form submission workflow](uploads.md); disabling it requires a different upload transport.

### Asset delivery

The npm-backed bundles (`FilePondAsset`, `CropperAsset`, and every plugin bundle) publish the `dist` files of their
package from the `@npm` alias, or link them from a CDN when `cdn` is `true`. CDN URLs follow
`{cdnUrl}/{package}@{version}/dist/{file}`, with the version read from the installed package, so both modes serve the
same release. `minified` selects the `.min` files; the default `null` serves them outside debug mode.

Set the mode for one widget with its `cdn` property, or for the whole application through the asset manager:

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

Application bundle settings take precedence over the widget's `cdn` value. Bundles already registered keep their
delivery mode. The package's own runtime and editor files are bundled with the extension; npm supplies the upstream
FilePond and Cropper.js distributions.

## Cropper.js editor

With `allowImageEdit`, the widget injects an `imageEditEditor` created by `yii2FilePond.cropper.createEditor()`
unless `config['imageEditEditor']` is set. The `cropper` property sets the initial ratio, the preset buttons, the
labels, and the Cropper.js constructor options:

```php
use yii2\extensions\filepond\FilePond;

echo FilePond::widget(
    [
        'name' => 'banner',
        'allowImageEdit' => true,
        'cropper' => [
            'aspectRatio' => '16:9',
            'aspectRatios' => ['16:9', '4:3', 'free'],
            'labels' => ['apply' => 'Crop'],
        ],
    ],
);
```

`imageCropAspectRatio` sets the initial selection ratio; it does not remove the editor's default preset buttons.
For a fixed square selection, combine `'imageCropAspectRatio' => '1:1'` with
`'cropper' => ['aspectRatios' => ['1:1']]`. Keep several presets, including `free` if desired, when users should be
able to change the selection ratio.

The editor opens a native `<dialog>` and confirms a FilePond `crop` metadata object computed in source image pixels.
The object also stores `selectionRatio`, the active preset as width divided by height or `null` for a free selection,
so reopening the editor restores the same constraint. The Image Transform plugin applies it on the client when
`allowImageTransform` is enabled, the Image Preview plugin reflects it in the thumbnail, and `metadata.crop.rect` is
available to `ImageCropper` on the server.

Set `cropper` only when `allowImageEdit` is enabled in the effective options; otherwise initialization fails.
Choose either client Image Transform or [server cropping](uploads.md#choose-where-to-crop) for the saved file.

The dialog styles use CSS custom properties prefixed with `--yii2-filepond-cropper-` and follow `prefers-color-scheme`.

## JavaScript runtime

The widget initializes at `View::POS_END`, so several inputs coexist on one page. Retrieve an instance with
`yii2FilePond.get(inputId)` when adding application callbacks; see [the JavaScript example](examples.md#accessing-the-instance-from-javascript).

## Translations

Labels come from the `yii.filepond` category registered by `Bootstrap`. Translations ship for Armenian, Chinese,
French, German, Polish, Portuguese, Russian, and Spanish. Override any label through `config` or by providing an
application message source for the category.

## Server-side helpers

The File Encode plugin submits JSON payloads with base64 content and metadata. Server validation detects MIME types
from decoded bytes with `finfo`. Client restrictions improve feedback but do not replace model validation.
The [upload guide](uploads.md) combines the validator, decoder, optional GD cropper, and saver in a complete request.

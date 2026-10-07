# Configuration reference

## Widget properties

`yii2\extensions\filepond\FilePond` extends `yii\widgets\InputWidget`, so `name`, `model`, `attribute`, and `options`
work as in any Yii2 input widget. The properties below map to FilePond options of the same name unless stated
otherwise.

| Property                    | Type                  | Default | Description                                                   |
| --------------------------- | --------------------- | ------- | ------------------------------------------------------------- |
| `acceptedFileTypes`         | `list<string>`        | `[]`    | MIME types or wildcards such as `image/*`.                    |
| `allowFileEncode`           | `bool`                | `true`  | Submit files as base64 JSON payloads.                         |
| `allowFilePoster`           | `bool`                | `false` | Render `metadata.poster` images inside items.                 |
| `allowFileRename`           | `bool`                | `false` | Enable `fileRenameFunction`.                                  |
| `allowFileSizeValidation`   | `bool`                | `true`  | Validate sizes on the client.                                 |
| `allowFileTypeValidation`   | `bool`                | `true`  | Validate types on the client.                                 |
| `allowImageCrop`            | `bool`                | `false` | Crop previews to `imageCropAspectRatio`.                      |
| `allowImageEdit`            | `bool`                | `false` | Open the Cropper.js editor for images.                        |
| `allowImageExifOrientation` | `bool`                | `true`  | Correct orientation from EXIF data.                           |
| `allowImagePreview`         | `bool`                | `true`  | Render image previews.                                        |
| `allowImageTransform`       | `bool`                | `false` | Apply crop, resize, and output transforms before submission.  |
| `allowMultiple`             | `bool`                | `false` | Accept several files; the input name receives `[]`.           |
| `allowPdfPreview`           | `bool`                | `false` | Render PDF previews.                                          |
| `cdn`                       | `bool`                | `false` | Link the libraries from the CDN instead of publishing them.   |
| `config`                    | `array<string,mixed>` | `[]`    | Any FilePond option; merged last and may hold `JsExpression`. |
| `cropper`                   | `array<string,mixed>` | `[]`    | Cropper.js editor options, see below.                         |
| `files`                     | `list<mixed>`         | `[]`    | Initial FilePond `files`.                                     |
| `imageCropAspectRatio`      | `string\|null`        | `null`  | Crop ratio in `width:height` format, for example `1:1`.       |
| `labelIdle`                 | `string\|null`        | `null`  | Drop area label; `null` uses the translated default.          |
| `maxFiles`                  | `int\|null`           | `null`  | Maximum number of files.                                      |
| `maxFileSize`               | `int\|string\|null`   | `null`  | Bytes or a FilePond size string such as `2MB`.                |
| `maxTotalFileSize`          | `int\|string\|null`   | `null`  | Bytes or a FilePond size string.                              |
| `minFileSize`               | `int\|string\|null`   | `null`  | Bytes or a FilePond size string.                              |
| `required`                  | `bool`                | `false` | Mark the input as required.                                   |

Options are assembled in this order, each layer overriding the previous one:

1. Localized labels for the core and the enabled validation plugins.
2. Typed properties, omitting `null` values and empty arrays.
3. `config`.

## Plugins and assets

Every `allow*` flag drives one plugin. When the effective option is `true`, the widget registers the plugin global
with `FilePond.registerPlugin()` and publishes its asset bundle; disabled plugins load nothing.

| Flag                        | Plugin                    | Asset bundle                                                    |
| --------------------------- | ------------------------- | --------------------------------------------------------------- |
| `allowFileEncode`           | File Encode               | `plugin\FileEncodeAsset`                                        |
| `allowFilePoster`           | File Poster               | `plugin\FilePosterAsset`                                        |
| `allowFileRename`           | File Rename               | `plugin\FileRenameAsset`                                        |
| `allowFileSizeValidation`   | File Validate Size        | `plugin\FileValidateSizeAsset`                                  |
| `allowFileTypeValidation`   | File Validate Type        | `plugin\FileValidateTypeAsset`                                  |
| `allowImageCrop`            | Image Crop                | `plugin\ImageCropAsset`                                         |
| `allowImageEdit`            | Image Edit and Cropper.js | `plugin\ImageEditAsset`, `CropperAsset`, `FilePondCropperAsset` |
| `allowImageExifOrientation` | Image EXIF Orientation    | `plugin\ImageExifOrientationAsset`                              |
| `allowImagePreview`         | Image Preview             | `plugin\ImagePreviewAsset`                                      |
| `allowImageTransform`       | Image Transform           | `plugin\ImageTransformAsset`                                    |
| `allowPdfPreview`           | PDF Preview               | `plugin\PdfPreviewAsset`                                        |

All bundles live under `yii2\extensions\filepond\asset`. The `Plugin` enumeration exposes the registration name,
npm package, option, and bundle class of each plugin.

### Bundle options

The npm-backed bundles (`FilePondAsset`, `CropperAsset`, and every plugin bundle) accept these properties through the
asset manager `bundles` configuration or through `registerWith()`:

| Property   | Type         | Default             | Description                                                                |
| ---------- | ------------ | ------------------- | -------------------------------------------------------------------------- |
| `cdn`      | `bool`       | `false`             | Link the files from the CDN pinned to the installed version.               |
| `cdnUrl`   | `string`     | `https://unpkg.com` | CDN base URL; files resolve as `{cdnUrl}/{package}@{version}/dist/{file}`. |
| `minified` | `bool\|null` | `null`              | Serve `.min` files; `null` means "outside debug mode".                     |

`FilePondAsset::registerWith($view, cdn: true)` seeds the delivery mode of the bundle and of its npm-backed
dependencies before they are instantiated; bundles already configured or registered keep their mode.

The package's own bundles (`FilePondWidgetAsset` and `FilePondCropperAsset`) accept `minified` with the same meaning.
Their `.min.js` and `.min.css` files are generated by `npm run build` and committed, so no Node.js toolchain is needed
in the application.

## Cropper.js editor

With `allowImageEdit`, the widget injects an `imageEditEditor` created by `yii2FilePond.cropper.createEditor()`
unless `config['imageEditEditor']` is set. The `cropper` property accepts:

| Key            | Type                   | Default                                 | Description                                                                      |
| -------------- | ---------------------- | --------------------------------------- | -------------------------------------------------------------------------------- |
| `aspectRatio`  | `string\|number\|null` | `imageCropAspectRatio`                  | Initial ratio; `null` or `free` allows any selection.                            |
| `aspectRatios` | `list<string>`         | `['free', '1:1', '16:9', '4:3', '3:2']` | Preset buttons; `free` renders the translated label.                             |
| `labels`       | `array<string,string>` | translated                              | `title`, `apply`, `cancel`, `reset`, `zoomIn`, `zoomOut`, `free`.                |
| `options`      | `array<string,mixed>`  | `[]`                                    | Options passed to the Cropper.js constructor, such as `template` or `container`. |

The editor opens a native `<dialog>` and confirms a FilePond `crop` metadata object computed in source image pixels.
The object also stores `selectionRatio`, the active preset as width divided by height or `null` for a free selection,
so reopening the editor restores the same constraint.
The Image Transform plugin applies it on the client when `allowImageTransform` is enabled, the Image Preview plugin
reflects it in the thumbnail, and `metadata.crop.rect` is available to `ImageCropper` on the server.

The dialog styles use CSS custom properties prefixed with `--yii2-filepond-cropper-` and follow
`prefers-color-scheme`.

## JavaScript runtime

The `FilePondWidgetAsset` bundle exposes `window.yii2FilePond`:

| Method                         | Description                                                 |
| ------------------------------ | ----------------------------------------------------------- |
| `create(id, plugins, options)` | Registers the plugins once and creates a FilePond instance. |
| `get(id)`                      | Returns the instance created for an input id, or `null`.    |
| `destroy(id)`                  | Destroys the instance and restores the original input.      |

The widget registers one `create()` call per input at `View::POS_END`, so several widgets coexist on one page and no
jQuery is required.

## Translations

Labels come from the `yii.filepond` category registered by `Bootstrap`. Translations ship for Armenian, Chinese,
French, German, Polish, Portuguese, Russian, and Spanish. Override any label through `config` or by providing an
application message source for the category.

## Server-side helpers

| Class                            | Purpose                                                                   |
| -------------------------------- | ------------------------------------------------------------------------- |
| `file\EncodedFile`               | Decodes File Encode payloads and exposes name, type, bytes, and metadata. |
| `file\FileSaver`                 | Writes files with sanitized names and content-derived extensions.         |
| `file\ImageCropper`              | Applies `metadata.crop.rect` with GD, honoring EXIF orientation.          |
| `validator\EncodedFileValidator` | Validates extensions, MIME types, sizes, and file count on the server.    |

### `EncodedFileValidator` options

| Property         | Type                         | Default | Description                                  |
| ---------------- | ---------------------------- | ------- | -------------------------------------------- |
| `extensions`     | `list<string>\|string\|null` | `null`  | Allowed extensions, list or comma-separated. |
| `mimeTypes`      | `list<string>\|string\|null` | `null`  | Allowed MIME types with `*` wildcards.       |
| `maxSize`        | `int\|null`                  | `null`  | Maximum size in bytes.                       |
| `minSize`        | `int\|null`                  | `null`  | Minimum size in bytes.                       |
| `maxFiles`       | `int`                        | `1`     | Maximum number of files; `0` disables it.    |
| `message`        | `string\|null`               | Yii     | Malformed payload message.                   |
| `tooBig`         | `string\|null`               | Yii     | Supports `{file}` and `{formattedLimit}`.    |
| `tooSmall`       | `string\|null`               | Yii     | Supports `{file}` and `{formattedLimit}`.    |
| `tooMany`        | `string\|null`               | Yii     | Supports `{limit}`.                          |
| `uploadRequired` | `string\|null`               | Yii     | Shown when every entry is empty.             |
| `wrongExtension` | `string\|null`               | Yii     | Supports `{file}` and `{extensions}`.        |
| `wrongMimeType`  | `string\|null`               | Yii     | Supports `{file}` and `{mimeTypes}`.         |

MIME types are detected from the decoded bytes with `finfo`, never from the client-reported type.

## Next steps

- 📚 [Installation Guide](installation.md)
- 💡 [Usage Examples](examples.md)
- 🧪 [Testing Guide](testing.md)

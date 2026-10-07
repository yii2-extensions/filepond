# Usage examples

## Plain input

```php
use yii2\extensions\filepond\FilePond;

echo FilePond::widget(['name' => 'attachment']);
```

The widget renders `<input class="filepond" id="w0" name="attachment" type="file">`, registers the default plugins
(File Encode, File Validate Size, File Validate Type, Image EXIF Orientation, and Image Preview), and creates the
FilePond instance at the end of the page.

## Active field with an avatar cropper

```php
use yii2\extensions\filepond\FilePond;

echo $form->field($model, 'avatar')->widget(
    FilePond::class,
    [
        'acceptedFileTypes' => ['image/jpeg', 'image/png', 'image/webp'],
        'allowImageEdit' => true,
        'allowImageTransform' => true,
        'imageCropAspectRatio' => '1:1',
        'maxFileSize' => '2MB',
        'config' => [
            'imageEditInstantEdit' => true,
            'imageTransformOutputMimeType' => 'image/png',
        ],
    ],
);
```

`allowImageEdit` loads Cropper.js and the editor adapter. `imageEditInstantEdit` opens the editor as soon as an
image is added, and `allowImageTransform` applies the confirmed crop before the encoded file is submitted.

## Cropping on the server

Disable the client transform to upload the original image and apply the crop rectangle with GD:

```php
use yii2\extensions\filepond\file\{EncodedFile, FileSaver, ImageCropper};

// In the view: 'allowImageEdit' => true, 'allowImageTransform' => false.

$saver = new FileSaver('@webroot/uploads/avatars');
$cropper = new ImageCropper(quality: 85);

foreach (EncodedFile::fromInput($model->avatar) as $file) {
    $path = $saver->save($cropper->crop($file), "user-{$model->id}");
}
```

`ImageCropper::crop()` returns the file unchanged when no rectangle is present, rotates JPEG images by their EXIF
orientation before cropping, and keeps PNG transparency.

## Validating uploads in the model

```php
use yii2\extensions\filepond\validator\EncodedFileValidator;

public function rules(): array
{
    return [
        [['avatar'], 'required'],
        [
            ['avatar'],
            EncodedFileValidator::class,
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
            'maxSize' => 2 * 1024 * 1024,
        ],
        [
            ['documents'],
            EncodedFileValidator::class,
            'extensions' => 'pdf',
            'mimeTypes' => 'application/pdf',
            'maxFiles' => 5,
        ],
    ];
}
```

The validator decodes the payload, so a renamed file is rejected by its real MIME type. `extensions` checks the
client file name while `FileSaver` derives the stored extension from the content, so `mimeTypes` is the rule that
decides what reaches the disk. Avoid `image/*` for files stored beneath the web root: it also accepts SVG, which can
carry scripts that run on your origin.

## Multiple documents with PDF previews

```php
use yii2\extensions\filepond\FilePond;

echo $form->field($model, 'documents')->widget(
    FilePond::class,
    [
        'acceptedFileTypes' => ['application/pdf'],
        'allowMultiple' => true,
        'allowPdfPreview' => true,
        'maxFiles' => 5,
        'maxTotalFileSize' => '20MB',
    ],
);
```

With `allowMultiple`, the input is named `Model[documents][]` and `EncodedFile::fromInput()` returns one file per
entry.

## Storing every submitted file

```php
use yii2\extensions\filepond\file\{EncodedFile, FileSaver};

$paths = (new FileSaver('@webroot/uploads/documents'))->saveAll(
    EncodedFile::fromInput($model->documents),
    "order-{$order->id}",
);
// order-42.pdf, order-42-1.pdf, ...
```

## Showing an already uploaded image

```php
use yii2\extensions\filepond\FilePond;

echo $form->field($model, 'logo')->widget(
    FilePond::class,
    [
        'allowFilePoster' => true,
        'files' => [
            [
                'source' => '/uploads/logo.png',
                'options' => [
                    'type' => 'local',
                    'file' => ['name' => 'logo.png', 'type' => 'image/png'],
                    'metadata' => ['poster' => '/uploads/logo.png'],
                ],
            ],
        ],
    ],
);
```

The File Poster plugin renders the existing image inside the item, and `type: 'local'` keeps it from being
re-submitted.

## JavaScript callbacks and custom labels

```php
use yii\web\JsExpression;
use yii2\extensions\filepond\FilePond;

echo FilePond::widget(
    [
        'name' => 'report',
        'allowFileRename' => true,
        'labelIdle' => 'Drop the report here',
        'cropper' => ['aspectRatios' => ['free', '1:1'], 'labels' => ['apply' => 'Crop']],
        'allowImageEdit' => true,
        'config' => [
            'credits' => false,
            'fileRenameFunction' => new JsExpression('(file) => `report-${Date.now()}${file.extension}`'),
            'onaddfile' => new JsExpression('(error, item) => console.log(item.filename)'),
        ],
    ],
);
```

`config` keys override typed properties and translated labels, and any `JsExpression` is emitted as raw JavaScript.

## Accessing the instance from JavaScript

```js
const pond = yii2FilePond.get("model-avatar");

pond.addFile("/uploads/sample.png");
yii2FilePond.destroy("model-avatar");
```

## Next steps

- 📚 [Installation Guide](installation.md)
- ⚙️ [Configuration Reference](configuration.md)
- 🧪 [Testing Guide](testing.md)

# Usage examples

Each example is a standalone PHP snippet that echoes its output, so the documentation site renders it as a live demo.
Snippets under the same `##` heading share their imports and variables.

For a complete application form with request loading, validation, and storage, use the [upload guide](uploads.md).

## Plain input

```php
use yii2\extensions\filepond\FilePond;

echo FilePond::widget(['name' => 'attachment']);
```

The widget renders `<input class="filepond" id="w0" name="attachment" type="file">`, registers the default plugins
(File Encode, File Validate Size, File Validate Type, Image EXIF Orientation, and Image Preview), and creates the
FilePond instance at the end of the page.

## Avatar with the image editor

```php
use yii2\extensions\filepond\FilePond;

echo FilePond::widget(
    [
        'name' => 'avatar',
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

In an active form, pass the same options without `name` to the field:
`$form->field($model, 'avatar')->widget(FilePond::class, [...])`.

## Multiple documents with PDF previews

```php
use yii2\extensions\filepond\FilePond;

echo FilePond::widget(
    [
        'name' => 'documents',
        'acceptedFileTypes' => ['application/pdf'],
        'allowMultiple' => true,
        'allowPdfPreview' => true,
        'maxFiles' => 5,
        'maxTotalFileSize' => '20MB',
    ],
);
```

With `allowMultiple`, the input name receives the `[]` suffix (`documents[]`, or `Model[documents][]` for an active
field), and `EncodedFile::fromInput()` returns one file per entry.

## Showing an already uploaded image

```php
use yii2\extensions\filepond\FilePond;

// The URL of the stored image, such as `/uploads/logo.png`; an inline SVG keeps the example self-contained.
$poster = 'data:image/svg+xml,' . rawurlencode(
    '<svg xmlns="http://www.w3.org/2000/svg" width="320" height="160"><rect width="320" height="160" fill="#4f46e5"/>'
    . '<text x="160" y="92" fill="#fff" font-family="sans-serif" font-size="32" text-anchor="middle">logo.png</text></svg>',
);

echo FilePond::widget(
    [
        'name' => 'logo',
        'allowFilePoster' => true,
        'config' => ['filePosterMaxHeight' => 160],
        'files' => [
            [
                'source' => 'logo.png',
                'options' => [
                    'type' => 'local',
                    'file' => ['name' => 'logo.png', 'size' => 2048, 'type' => 'image/png'],
                    'metadata' => ['poster' => $poster],
                ],
            ],
        ],
    ],
);
```

The File Poster plugin renders the existing image inside the item. With `type: 'local'`, the form submits the
`source` identifier (`logo.png` here) instead of new encoded bytes. Authorize and handle that identifier separately
before encoded-upload validation; see [stored-image handling](uploads.md#multiple-files-and-stored-images).

## JavaScript callbacks and custom labels

```php
use yii\web\JsExpression;
use yii2\extensions\filepond\FilePond;

echo FilePond::widget(
    [
        'name' => 'report',
        'allowFileRename' => true,
        'allowImageEdit' => true,
        'cropper' => ['aspectRatios' => ['free', '1:1'], 'labels' => ['apply' => 'Crop']],
        'labelIdle' => 'Drop the report here',
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

The widget runtime keeps one instance per input id:

```js
const pond = yii2FilePond.get("uploadform-avatar");

pond.addFile("/uploads/sample.png");
yii2FilePond.destroy("uploadform-avatar");
```

## Handling uploads on the server

The File Encode plugin submits every file as a JSON string with the name, the client type, the base64 content, and
the metadata (such as the crop rectangle of the editor). The snippets of this section build those values by hand, so
they run without a browser; in an application they come from `$model->load(Yii::$app->request->post())`.
The [upload guide](uploads.md) shows that application flow; the snippets below demonstrate the individual helpers.

### Validating uploads in the model

```php
use yii\base\Model;
use yii\helpers\Html;
use yii2\extensions\filepond\validator\EncodedFileValidator;

final class UploadForm extends Model
{
    public mixed $avatar = null;
    public mixed $documents = null;

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
}

// A 1x1 PNG image renamed to `contract.pdf`.
$model = new UploadForm(
    [
        'documents' => [
            json_encode(
                [
                    'name' => 'contract.pdf',
                    'type' => 'application/pdf',
                    'data' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
                ],
            ),
        ],
    ],
);

$model->validate();

echo Html::errorSummary($model);
```

The validator decodes the payload, so the renamed file is rejected by its real MIME type. `extensions` checks the
client file name while `FileSaver` derives the stored extension from the content, so `mimeTypes` is the rule that
decides what reaches the disk. Avoid `image/*` for files stored beneath the web root: it also accepts SVG, which can
carry scripts that run on your origin.

### Cropping on the server

Disable the client transform (`'allowImageEdit' => true, 'allowImageTransform' => false` in the widget) to upload the
original image, then apply the crop rectangle of the editor with GD:

```php
use yii2\extensions\filepond\file\{EncodedFile, FileSaver, ImageCropper};

// A 400x300 PNG image with the square selected in the editor.
ob_start();
imagepng(imagecreatetruecolor(400, 300));

$model->avatar = json_encode(
    [
        'name' => 'photo.png',
        'type' => 'image/png',
        'data' => base64_encode(ob_get_clean()),
        'metadata' => ['crop' => ['rect' => ['x' => 50, 'y' => 0, 'width' => 300, 'height' => 300]]],
    ],
);

$saver = new FileSaver('@runtime/uploads/avatars');

// Limits for this 400x300 demo image; applications need their own measured processing budget.
$limits = [
    'maxPixels' => 1_048_576, 
    'maxDimension' => 2048, 
    'maxInputBytes' => 2_000_000,
];

$cropper = new ImageCropper(
    maxPixels: $limits['maxPixels'],
    maxDimension: $limits['maxDimension'],
    maxInputBytes: $limits['maxInputBytes'],
    quality: 85,
);

if ($model->validate(['avatar'])) {
    try {
        foreach (EncodedFile::fromInput($model->avatar) as $file) {
            $path = $saver->save($cropper->crop($file), 'user-42');

            [$width, $height] = getimagesize($path);

            echo basename($path), " ({$width}x{$height})";
        }
    } catch (\RuntimeException $exception) {
        Yii::error($exception, __METHOD__);
        $model->addError('avatar', 'The avatar could not be processed or stored.');
    }
}
```

Configure the three positive `uploads` limits from the application's measured processing budget; see
[server cropping](uploads.md#choose-where-to-crop) for the policy and worker-memory example. Source bytes, dimensions,
and pixel counts are checked before decoding, including when the requested output crop is small.
`ImageCropper::crop()` returns the file unchanged without decoding when no rectangle is present, rotates JPEG images
by their EXIF orientation before cropping, and keeps PNG transparency.

### Storing every submitted file

```php
use yii2\extensions\filepond\file\{EncodedFile, FileSaver};

$pdf = base64_encode("%PDF-1.7\n%%EOF\n");

$model->documents = [
    json_encode(['name' => 'invoice.pdf', 'type' => 'application/pdf', 'data' => $pdf]),
    json_encode(['name' => 'invoice.pdf', 'type' => 'application/pdf', 'data' => $pdf]),
];

if ($model->validate(['documents'])) {
    $paths = (new FileSaver('@runtime/uploads/documents'))->saveAll(
        EncodedFile::fromInput($model->documents),
        'order-42',
    );

    echo implode(', ', array_map(basename(...), $paths));
}
```

With a base name, the first file keeps it and the following ones receive a numeric suffix (`order-42.pdf`,
`order-42-1.pdf`). Store uploads outside the web root unless they must be served directly.

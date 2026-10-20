# Receive and store uploads

This workflow uses the File Encode plugin to submit files with a normal Yii form. The browser sends JSON with
base64 content in the model attribute, rather than a PHP `UploadedFile`. Keep `allowFileEncode` enabled and load
the posted value with `Model::load()`.

The example accepts one avatar, validates its decoded bytes, saves it outside the web root, and redirects after
success. Use the application's existing authentication and namespace layout; the paths below follow a Yii 22
application with `src/` and `resources/views/`.

## Validate the model attribute

Create `src/models/AvatarUploadForm.php`:

```php
<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;
use yii2\extensions\filepond\validator\EncodedFileValidator;

final class AvatarUploadForm extends Model
{
    public mixed $avatar = null;

    public function rules(): array
    {
        return [
            [
                ['avatar'],
                EncodedFileValidator::class,
                'skipOnEmpty' => false,
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                'maxFiles' => 1,
                'maxSize' => 2_000_000,
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return ['avatar' => 'Avatar'];
    }
}
```

`skipOnEmpty: false` makes an upload required. Omit it for an optional replacement and save only when the decoded
file list is nonempty. MIME validation uses `finfo` on the bytes; `extensions` checks the client name. Client widget
limits do not replace either check. FilePond interprets `2MB` as 2,000,000 bytes; `maxSize` uses bytes, so the example
sets the same decimal limit on both sides. Use an explicit raster allow-list when images will be served from your origin:
`image/*` also admits SVG.

## Load, validate, then save

Create `src/controllers/AvatarController.php`:

```php
<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\AvatarUploadForm;
use RuntimeException;
use Yii;
use yii\filters\{AccessControl, VerbFilter};
use yii\web\{Controller, Response};
use yii2\extensions\filepond\file\{EncodedFile, FileSaver};

final class AvatarController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index'],
                'rules' => [['actions' => ['index'], 'allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['index' => ['get', 'post']],
            ],
        ];
    }

    public function actionIndex(): Response|string
    {
        $model = new AvatarUploadForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $file = EncodedFile::fromInput($model->avatar)[0];
            $name = 'avatar-' . bin2hex(random_bytes(16));

            try {
                $path = (new FileSaver('@runtime/uploads/avatars'))->save($file, $name);

                Yii::$app->session->setFlash('success', 'Avatar saved as ' . basename($path) . '.');

                return $this->redirect(['index']);
            } catch (RuntimeException $exception) {
                Yii::error($exception, __METHOD__);
                $model->addError('avatar', 'The avatar could not be stored. Please try again.');
            }
        }

        return $this->render('index', ['model' => $model]);
    }
}
```

The required validator guarantees one valid file before the decoder is indexed. `FileSaver` creates the target
directory and derives the extension from the detected MIME type. Passing a generated base name avoids using the
client name as the stored identifier; saving to the same path would replace the file.

In a profile application, persist the returned path or storage identifier against the authenticated user's record
before redirecting. Handle any database failure and remove the new file if the record cannot be saved; keep the
previous avatar until its replacement is committed. The example above only stores the file and reports its name.

## Render the form

Create `resources/views/avatar/index.php`:

```php
<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii2\extensions\filepond\FilePond;

/** @var app\models\AvatarUploadForm $model */
?>
<h1>Upload an avatar</h1>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <p><?= Html::encode(Yii::$app->session->getFlash('success')) ?></p>
<?php endif; ?>

<?php $form = ActiveForm::begin(['enableClientScript' => false]); ?>
    <?= Html::errorSummary($model) ?>
    <?= $form->field($model, 'avatar')->widget(
        FilePond::class,
        [
            'acceptedFileTypes' => ['image/jpeg', 'image/png', 'image/webp'],
            'allowImageEdit' => true,
            'allowImageTransform' => true,
            'imageCropAspectRatio' => '1:1',
            'cropper' => ['aspectRatios' => ['1:1']],
            'maxFileSize' => '2MB',
            'config' => ['imageEditInstantEdit' => true],
        ],
    ) ?>
    <?= Html::submitButton('Save avatar') ?>
<?php ActiveForm::end(); ?>
```

Open the `avatar/index` route while signed in. The form retains Yii's CSRF input and uses server validation; its
client script is disabled so this example needs no jQuery form-validation layer. The FilePond widget initializes
its own runtime. See [installation](installation.md) for the npm alias and assets.

This avatar form offers only a square crop. Other uploads can offer several ratios; see
[editor configuration](configuration.md#cropperjs-editor).

## Choose where to crop

The form above enables Image Transform: the browser applies the confirmed crop, and the server validates and
saves those transformed bytes. Image Preview alone changes the thumbnail, not the submitted content.

For server processing, set `allowImageTransform` to `false` in the form, keep `allowImageEdit` enabled, and apply
`ImageCropper` to the validated original before saving. Its required positive limits reject excessive input bytes,
source dimensions, and source pixel count before GD decoding. There is no unlimited default.

Configure the upload policy in the application's parameters. This example accepts at most 1,048,576 source pixels,
no dimension above 2048, and the same 2,000,000-byte limit as the model:

```php
'uploads' => [
    'maxPixels' => 1_048_576,
    'maxDimension' => 2048,
    'maxInputBytes' => 2_000_000,
],
```

```php
use yii2\extensions\filepond\file\ImageCropper;

// Inside the upload-processing try block, before FileSaver::save().
$limits = Yii::$app->params['uploads'];
$file = (new ImageCropper(
    maxPixels: $limits['maxPixels'],
    maxDimension: $limits['maxDimension'],
    maxInputBytes: $limits['maxInputBytes'],
    quality: 85,
))->crop($file);
```

The example admits a 1024x1024 or 2048x512 original and rejects larger originals before decoding. It represents an
avatar policy, not a universal memory limit. For example, a 256 MiB worker allocation could reserve 100 MiB for a
measured application baseline, 16 MiB for byte buffers, 64 MiB for image processing, and 76 MiB of headroom. Confirm
that allocation by profiling the complete application with its accepted formats, aspect ratios, EXIF rotations,
full-size crops, and encoding. Retune the policy before accepting larger originals. Budget all concurrent workers
within the deployment's available memory.

Use process RSS or peak resident memory when profiling; native GD allocations may be absent from PHP's memory
accounting. Enforce process or container memory limits separately when a failed image-processing worker must not
exhaust the serving application. These predecode checks are not a hard limit on the whole process. Request and
base64 body limits remain necessary before creating an `EncodedFile`.

This requires `gd`. The editor supplies `metadata.crop.rect` in source-image pixels. The cropper intersects the
rectangle with the image bounds and honors JPEG EXIF orientation when `exif` is available. It returns an unchanged
file without decoding if no usable rectangle exists; this no-op does not validate the image. Limit or decode
failures throw `RuntimeException`, handled by the upload-processing catch block. If the application requires square
output or fixed dimensions, enforce that requirement on the resulting image rather than relying on client metadata.

Do not apply the original crop rectangle again to bytes already transformed by the browser. If the application
also limits the transformed output's size or format, validate those resulting bytes before saving.

## Multiple files and stored images

For multiple uploads, set `allowMultiple` and `maxFiles` on the widget, raise the validator's `maxFiles`, and pass
the validated `EncodedFile::fromInput()` list to `FileSaver::saveAll()`. The input receives a `[]` suffix; each
entry remains an encoded payload. `maxSize` limits each decoded file, so enforce any aggregate application quota
separately.

To display an existing file, configure `files` with `type: 'local'` and a poster URL as shown in
[the existing-image example](examples.md#showing-an-already-uploaded-image). The form submits the item's `source`
identifier, such as `logo.png`, rather than new encoded bytes. That identifier is not a valid File Encode payload.

For an optional replacement form, omit `skipOnEmpty: false` and resolve the current stored identifier from the
record the authenticated user is authorized to edit. After loading the request and before validation, convert only
an exact match with that authorized identifier to `null`:

```php
// $currentAvatarId comes from the authorized record, never from the request.
if ($currentAvatarId !== null && $model->avatar === $currentAvatarId) {
    $model->avatar = null;
}
```

Then validate normally and save only when `EncodedFile::fromInput()` returns a nonempty list. Other submitted values
still go through the encoded validator; do not discard arbitrary non-JSON strings. An omitted upload or a retained
stored identifier is not a deletion instruction. Handle deletion through a separate authorized application action.

## Request limits and delivery

Base64 adds roughly one third to the binary size, plus JSON and form overhead. Set PHP `post_max_size` and the web
server's body limit for the total encoded request, including all files. `upload_max_filesize` does not bound these
ordinary POST fields. Keep the model's decoded size and count limits independently of the request limit.

Files under `@runtime/uploads` have no public URL. Serve them through an authorized application action, or move
approved public assets to a dedicated storage location and save their generated identifier. Set delivery headers
from the stored content and encode client names whenever displaying them. `FileSaver` returns a filesystem path,
not a URL.

## Verify the flow

- Submit without a file: the model renders an error and writes nothing.
- Submit a valid raster image: the action redirects and a generated filename appears under `runtime/uploads/avatars`.
- Submit a renamed or oversized file: server validation rejects it even if client checks are bypassed.
- Confirm a crop: inspect the saved image's dimensions, testing client transformation and server cropping separately.
- Test storage failure: the form shows an error; no success redirect is reported.

The [API reference](https://yii2-framework.github.io/docs/extensions/filepond/reference/) documents exact helper
signatures, property types, defaults, and exceptions.

[Back to documentation](../README.md#documentation)

<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond;

use JsonException;
use PHPForge\Html\Helper\CssClass;
use PHPForge\Html\Helper\Utils;
use RuntimeException;
use Yii2\Extensions\FilePond\Asset\FilePondAsset;
use Yii2\Extensions\FilePond\Asset\FilePondCdnAsset;
use Yii2\Extensions\FilePond\Asset\FilePondProdAsset;
use Yii;
use yii\widgets\InputWidget;

final class FilePond extends InputWidget
{
    /**
     * @var array The HTML attributes for the input tag.
     */
    public array $attributes = [];
    /**
     * @var array The accepted file types. Can be mime types or wild cards.
     * For instance ['image/*'] will accept all images. ['image/png', 'image/jpeg'] will only accept PNGs and JPEGs.
     */
    public array $acceptedFileTypes = [];
    /**
     * @var bool Whether to allow file type validation.
     */
    public bool $allowFileTypeValidation = true;
    /**
     * @var bool Whether to allow file rename.
     */
    public bool $allowFileRename = false;
    /**
     * @var bool Whether to allow file size validation.
     */
    public bool $allowFileValidateSize = true;
    /**
     * @var bool Whether to allow image crop.
     */
    public bool $allowImageCrop = false;
    /**
     * @var bool Whether to allow image exif orientation.
     */
    public bool $allowImageExifOrientation = true;
    /**
     * @var bool Whether to allow image preview.
     */
    public bool $allowImagePreview = true;
    /**
     * @var bool Whether to allow image transform.
     */
    public bool $allowImageTransform = false;
    /**
     * @var bool Whether to allow multiple file upload.
     */
    public bool $allowMultiple = false;
    /**
     * @var bool Whether to allow PDF preview.
     */
    public bool $allowPdfPreview = false;
    /**
     * @var string The CSS class for the filepond container.
     */
    public string $cssClass = '';
    /**
     * @var array The FilePond configuration.
     */
    public array $config = [];
    /**
     * @var string The environment to use.
     */
    public string $environment = '';
    /**
     * @var string The file rename function.
     *
     * use: `fileRenameFunction: (file) => return `my_new_name${file.extension}`;
     */
    public string $fileRename = '';
    /**
     * @var string The file validate type detect type function.
     *
     * ```JS
     * fileValidateTypeDetectType: (source, type) =>
     *     new Promise((resolve, reject) => {
     *     // Do custom type detection here and return with promise
     *
     *     resolve(type);
     * }),
     * ```
     *
     * @link https://pqina.nl/filepond/docs/api/plugins/file-validate-type/#custom-type-detection
     */
    public string $fileValidateTypeDetectType = '';
    /**
     * @var string The file validate When message shown to indicate the allowed file types.
     */
    public string $fileValidateTypeLabelExpectedTypes = '';
    /**
     * @var string The aspect ratio of the crop in human-readable format, for example, '1:1' or '16:10'.
     */
    public string|null $imageCropAspectRatio = null;
    /**
     * @var string The image preview height.
     *
     * Fixed image preview height, overrides min and max preview height.
     */
    public string|null $imagePreviewHeight = null;
    /**
     * @var bool Whether to show the image preview markup.
     */
    public bool $imagePreviewMarkupShow = true;
    /**
     * @var string The image preview max file size.
     *
     * Maximum file size for images to preview immediately, if files are larger and the browser doesn't support
     * createImageBitmap the preview is queued till FilePond is in rest state.
     *
     * By default, no maximum file size is defined, expects a string, like `2MB` or `500KB`.
     */
    public string|null $imagePreviewMaxFileSize = null;
    /**
     * @var int The image preview max height.
     *
     * Maximum height of the image preview in pixels.
     */
    public int $imagePreviewMaxHeight = 256;
    /**
     * @var int The image preview max instant preview file size.
     *
     * Maximum file size for images to preview immediately, if files are larger and the browser doesn't support
     * createImageBitmap the preview is queued till FilePond is in rest state.
     */
    public int $imagePreviewMaxInstantPreviewFileSize = 1000000;
    /**
     * @var int The image preview min height.
     *
     * Minimum height of the image preview in pixels.
     */
    public int $imagePreviewMinHeight = 44;
    /**
     * @var string The image preview transparency indicator.
     *
     * Show a grid behind the preview, set to a color value (for example '#f00') to set transparent image background
     * color.
     *
     * Please note that this is only for preview purposes.
     *
     * The background color or grid isn't embedded in the output image.
     */
    public string|null $imagePreviewTransparencyIndicator = null;
    /**
     * @var array The image transform after create blob.
     *
     * A hook to make changes to the file after the file has been created.
     */
    public array|null $imageTransformAfterCreateBlob = null;
    /**
     * @var array The image transform before create blob.
     *
     * A hook to make changes to the canvas before the file is created.
     */
    public array|null $imageTransformBeforeCreateBlob = null;
    /**
     * @var int The image transform output quality.
     *
     * A number between 0 and 100 indicating image quality (e.g. 92 => 92%).
     */
    public int|null $imageTransformOutputQuality = null;
    /**
     * @var array The image transform client transforms.
     *
     * An array of transforms to apply on the client, useful if we, for instance, want to do resizing on the client but
     * cropping on the server. Null means apply all transforms ('resize', 'crop').
     */
    public array|null $imageTransformClientTransforms = null;
    /**
     * @var string The image transform output quality mode.
     *
     * Should output quality be enforced, set the 'optional' to only apply when a transform is required due to other
     * requirements (e.g. resize or crop).
     */
    public string $imageTransformOutputQualityMode = 'always';
    /**
     * @var bool Whether to strip the image head.
     */
    public bool $imageTransformOutputStripImageHead = true;
    /**
     * @var array The image transform variants.
     *
     * An object that can be used to output many files based on different transform instructions.
     */
    public array|null $imageTransformVariants = null;
    /**
     * @var bool Whether the image transform variants include original.
     *
     * Should the transform plugin output the original file.
     */
    public bool $imageTransformVariantsIncludeDefault = true;
    /**
     * @var string The image transform variants default name.
     *
     * The name to use in front of the file name.
     */
    public string|null $imageTransformVariantsDefaultName = null;
    /**
     * @var bool Whether the image transform variants include original.
     *
     * Should the transform plugin output the original file.
     */
    public bool $imageTransformVariantsIncludeOriginal = false;
    /**
     * @var string The label to show when there are no files.
     */
    public string $labelIdle = 'Drag & Drop your files or <span class="filepond--label-action"> Browse </span>';
    /**
     * @var string Label for max files size.
     */
    public string $labelMaxFileSize = '';
    /**
     * @var string Label for max file size exceeded.
     */
    public string $labelMaxFileSizeExceeded = '';
    /**
     * @var string Label for max total file size.
     */
    public string $labelMaxTotalFileSize = '';
    /**
     * @var string Label for max total file size exceeded.
     */
    public string $labelMaxTotalFileSizeExceeded = '';
    /**
     * @var string Label for a file type not allowed error.
     */
    public string $labelFileTypeNotAllowed = '';
    /**
     * @var string The default file to load.
     */
    public string $loadFileDefault = '';
    /**
     * @var int The maximum number of files allowed.
     */
    public int $maxFiles = 1;
    /**
     * @var string The maximum file size allowed.
     */
    public string|null $maxFileSize = null;
    /**
     * @var string The maximum total file size allowed.
     */
    public string|null $maxTotalFileSize = null;
    /**
     * @var string The minimum file size allowed.
     */
    public string|null $minFileSize = null;
    /**
     * @phpstan-var string[] The default plugins to load.
     */
    public array $pluginDefault = [
        'FilePondPluginFileEncode',
        'FilePondPluginFileValidateSize',
        'FilePondPluginFileValidateType',
        'FilePondPluginImageExifOrientation',
        'FilePondPluginImagePreview',
    ];
    /**
     * @var int The pdf preview height.
     */
    public int $pdfPreviewHeight = 320;
    /**
     * @var string The pdf component extra params.
     */
    public string $pdfComponentExtraParams = 'toolbar=0&view=fit&page=1';
    /**
     * @var bool Whether the field is required.
     */
    public bool $required = false;

    public function init(): void
    {
        $this->config = array_merge(
            [
                'acceptedFileTypes' => $this->acceptedFileTypes,
                'allowFileRename' => $this->allowFileRename,
                'allowFileTypeValidation' => $this->allowFileTypeValidation,
                'allowFileValidateSize' => $this->allowFileValidateSize,
                'allowImageCrop' => $this->allowImageCrop,
                'allowImageExifOrientation' => $this->allowImageExifOrientation,
                'allowImagePreview' => $this->allowImagePreview,
                'allowImageTransform' => $this->allowImageTransform,
                'allowMultiple' => $this->allowMultiple,
                'className' => $this->cssClass,
                'fileValidateTypeLabelExpectedTypes' => Yii::t(
                    'yii.filepond',
                    'Expects {allButLastType} or {lastType}',
                ),
                'imageCropAspectRatio' => $this->imageCropAspectRatio,
                'imagePreviewHeight' => $this->imagePreviewHeight,
                'imagePreviewMarkupShow' => $this->imagePreviewMarkupShow,
                'imagePreviewMaxFileSize' => $this->imagePreviewMaxFileSize,
                'imagePreviewMaxHeight' => $this->imagePreviewMaxHeight,
                'imagePreviewMaxInstantPreviewFileSize' => $this->imagePreviewMaxFileSize,
                'imagePreviewMinHeight' => $this->imagePreviewMinHeight,
                'imagePreviewTransparencyIndicator' => $this->imagePreviewTransparencyIndicator,
                'imageTransformAfterCreateBlob' => $this->imageTransformAfterCreateBlob,
                'imageTransformBeforeCreateBlob' => $this->imageTransformBeforeCreateBlob,
                'imageTransformClientTransforms' => $this->imageTransformClientTransforms,
                'imageTransformOutputQuality' => $this->imageTransformOutputQuality,
                'imageTransformOutputQualityMode' => $this->imageTransformOutputQuality,
                'imageTransformOutputStripImageHead' => $this->imageTransformOutputStripImageHead,
                'imageTransformVariants' => $this->imageTransformVariants,
                'imageTransformVariantsDefaultName' => $this->imageTransformVariantsDefaultName,
                'imageTransformVariantsIncludeOriginal' => $this->imageTransformVariantsIncludeDefault,
                'labelFileTypeNotAllowed' => Yii::t('yii.filepond', 'File type not allowed'),
                'labelIdle' => $this->labelIdle,
                'labelMaxFileSize' => Yii::t('yii.filepond', 'Maximum file size is {filesize}'),
                'labelMaxFileSizeExceeded' => Yii::t('yii.filepond', 'File is too large'),
                'labelMaxTotalFileSize' => Yii::t('yii.filepond', 'Maximum total file size is {filesize}'),
                'labelMaxTotalFileSizeExceeded' => Yii::t('yii.filepond', 'Maximum total size exceeded'),
                'maxFiles' => $this->maxFiles,
                'maxFileSize' => $this->maxFileSize,
                'maxTotalFileSize' => $this->maxTotalFileSize,
                'minFileSize' => $this->minFileSize,
                'pdfPreviewHeight' => $this->pdfPreviewHeight,
                'pdfComponentExtraParams' => $this->pdfComponentExtraParams,
                'required' => $this->required,
            ],
            $this->config,
        );
    }

    public function run(): string
    {
        if ($this->model === null) {
            throw new RuntimeException('The model is not set.');
        }

        if ($this->attribute === null) {
            throw new RuntimeException('The attribute is not set.');
        }

        $this->registerClientScript();

        return $this->renderInputFile();
    }

    /**
     * @throws JsonException
     */
    private function getScript(): string
    {
        $closure = $this->fileRename;

        if ($this->fileValidateTypeDetectType !== '') {
            $closure = "{$this->fileValidateTypeDetectType} {$closure}";
        }

        $id = Utils::generateInputId($this->model->formName(), $this->attribute);
        $loadFileDefault = $this->loadFileDefault;
        $pluginConfig = implode(', ', $this->pluginDefault);
        $setOptions = json_encode($this->config, JSON_THROW_ON_ERROR);

        return <<<JS
        FilePond.registerPlugin($pluginConfig)
        FilePond.setOptions($setOptions)

        const loadFileDefault = "$loadFileDefault"
        const pond = FilePond.create(document.querySelector('input[type="file"][id="$id"]'), {$closure})

        if (loadFileDefault !== '') {
            pond.addFiles(loadFileDefault)
        }
        JS;
    }

    private function registerClientScript(): void
    {
        $view = $this->getView();

        if ($this->environment === '') {
            $this->environment = YII_ENV;
        }

        match ($this->environment) {
            'cdn' => FilePondCdnAsset::register($view),
            'prod' => FilePondProdAsset::register($view),
            default => FilePondAsset::register($view),
        };

        $view->registerJs($this->getScript());
    }

    /**
     * @return string the generated input tag.
     */
    private function renderInputFile(): string
    {
        $attributes = $this->attributes;

        if (array_key_exists('allowMultiple', $this->config) && $this->config['allowMultiple']) {
            $attributes['multiple'] = true;
        }

        if (array_key_exists('className', $this->config) && is_string($this->config['className'])) {
            CssClass::add($attributes, $this->config['className']);
        }

        if (array_key_exists('required', $this->config) && $this->config['required']) {
            $attributes['required'] = true;
        }

        CssClass::add($attributes, 'filepond');

        return File::widget($this->model, $this->attribute)->attributes($attributes)->render();
    }
}

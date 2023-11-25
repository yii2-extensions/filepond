<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond;

use JsonException;
use PHPForge\Html\Helper\CssClass;
use PHPForge\Html\Helper\Utils;
use PHPForge\Html\Input;
use Yii2\Extensions\FilePond\Asset\FilePondAsset;
use Yii2\Extensions\FilePond\Asset\FilePondCdnAsset;
use Yii;
use yii\widgets\InputWidget;

final class FilePond extends InputWidget
{
    public array $acceptedFileTypes = [];
    public bool $allowFileTypeValidation = true;
    public bool $allowFileRename = false;
    public bool $allowFileValidateSize = true;
    public bool $allowImageCrop = false;
    public bool $allowImageExifOrientation = true;
    public bool $allowImagePreview = true;
    public bool $allowImageTransform = false;
    public bool $allowMultiple = false;
    public bool $allowPdfPreview = false;
    public string $cssClass = '';
    public bool $cdn = false;
    public array $config = [];
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
    public string $fileValidateTypeLabelExpectedTypes = '';
    public string|null $imageCropAspectRatio = null;
    /**
     * @var string The image preview height.
     *
     * Fixed image preview height, overrides min and max preview height.
     */
    public string|null $imagePreviewHeight = null;
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
    public string $labelIdle = '';
    public string $labelMaxFileSize = '';
    public string $labelMaxFileSizeExceeded = '';
    public string $labelMaxTotalFileSize = '';
    public string $labelMaxTotalFileSizeExceeded = '';
    public string $labelFileTypeNotAllowed = '';
    public string $loadFileDefault = '';
    public int $maxFiles = 1;
    public string|null $maxFileSize = null;
    public string|null $maxTotalFileSize = null;
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
    public int $pdfPreviewHeight = 320;
    public string $pdfComponentExtraParams = 'toolbar=0&view=fit&page=1';
    public bool $required = false;

    private string $id = '';

    public function init(): void
    {
        parent::init();

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
                'labelIdle' => $this->labelIdle === ''
                    ? Yii::t(
                        'yii.filepond',
                        'Drag & Drop your files or <span class="filepond--label-action"> Browse </span>',
                    )
                    : $this->labelIdle,
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

        $this->id = $this->hasModel()
            ? Utils::generateInputId($this->model->formName(), $this->attribute)
            : $this->getId() . '-filepond';
    }

    public function run(): string
    {
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

        $loadFileDefault = $this->loadFileDefault;
        $pluginConfig = implode(', ', $this->pluginDefault);
        $setOptions = json_encode($this->config, JSON_THROW_ON_ERROR);

        return <<<JS
        FilePond.registerPlugin($pluginConfig)
        FilePond.setOptions($setOptions)

        const loadFileDefault = "$loadFileDefault"
        const pond = FilePond.create(document.querySelector('input[type="file"][id="$this->id"]'), {$closure})

        if (loadFileDefault !== '') {
            pond.addFiles(loadFileDefault)
        }
        JS;
    }

    private function registerClientScript(): void
    {
        $view = $this->getView();

        match ($this->cdn) {
            true => FilePondCdnAsset::register($view),
            default => FilePondAsset::register($view),
        };

        $view->registerJs($this->getScript());
    }

    /**
     * @return string the generated input tag.
     */
    private function renderInputFile(): string
    {
        $name = $this->name;
        $options = $this->options;

        if (isset($options['class']) && str_contains($options['class'], 'form-control')) {
            $options['class'] = str_replace('form-control', '', $options['class']);
        }

        if (array_key_exists('allowMultiple', $this->config) && $this->config['allowMultiple']) {
            $options['multiple'] = true;
        }

        if (array_key_exists('className', $this->config) && is_string($this->config['className'])) {
            $class = str_replace('form-control', '', $this->config['className']);
            CssClass::add($options, $class);
        }

        if (array_key_exists('required', $this->config) && $this->config['required']) {
            $options['required'] = true;
        }

        CssClass::add($options, 'filepond');

        $name = match ($this->hasModel()) {
            true => Utils::generateArrayableName(Utils::generateInputName($this->model->formName(), $this->attribute)),
            default => Utils::generateArrayableName($name),
        };

        // input type="file" not supported value attribute.
        unset($options['id'], $options['placeholder'], $options['value']);

        return match ($this->hasModel()) {
            true => Input::widget()
                ->attributes($options)
                ->id($this->id)
                ->name($name)
                ->type('file')
                ->render(),
            default => Input::widget()
                ->attributes($options)
                ->id($this->id)
                ->name($name)
                ->type('file')
                ->render(),
        };
    }
}

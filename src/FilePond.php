<?php

declare(strict_types=1);

namespace yii2\extensions\filepond;

use UIAwesome\Html\Form\InputFile;
use UIAwesome\Html\Helper\CSSClass;
use Yii;
use yii\base\{InvalidConfigException, Model};
use yii\helpers\{Html, Json};
use yii\web\{JsExpression, View};
use yii\widgets\InputWidget;
use yii2\extensions\filepond\asset\{CropperAsset, FilePondAsset, FilePondCropperAsset, FilePondWidgetAsset, Plugin};
use yii2\extensions\filepond\exception\Message;

use function array_diff;
use function array_filter;
use function array_key_exists;
use function array_map;
use function array_replace;
use function array_values;
use function is_array;
use function is_string;
use function preg_match;
use function preg_split;

/**
 * Renders a FilePond file input with plugin management, localized labels, and optional Cropper.js image editing.
 *
 * Plugins and their asset bundles are registered from the `allow*` flags. Every other FilePond option is passed
 * through {@see $config}, where JavaScript callbacks are expressed as {@see JsExpression}.
 *
 * @see https://pqina.nl/filepond/docs/api/instance/properties/
 */
final class FilePond extends InputWidget
{
    /**
     * Message category used for the widget labels.
     */
    public const string TRANSLATION_CATEGORY = 'yii.filepond';
    /**
     * @var list<string> Accepted MIME types or wildcards such as `image/*`, enforced by the File Validate Type plugin.
     */
    public array $acceptedFileTypes = [];
    /**
     * Whether to encode files as base64 JSON payloads submitted with the form.
     */
    public bool $allowFileEncode = true;
    /**
     * Whether to render a poster image for items that carry `metadata.poster`.
     */
    public bool $allowFilePoster = false;
    /**
     * Whether to rename files on the client through `fileRenameFunction`.
     */
    public bool $allowFileRename = false;
    /**
     * Whether to validate file sizes on the client.
     */
    public bool $allowFileSizeValidation = true;
    /**
     * Whether to validate file types on the client.
     */
    public bool $allowFileTypeValidation = true;
    /**
     * Whether to crop image previews to {@see}.
     */
    public bool $allowImageCrop = false;
    /**
     * Whether to open the Cropper.js editor for image items.
     */
    public bool $allowImageEdit = false;
    /**
     * Whether to correct image orientation from EXIF data.
     */
    public bool $allowImageExifOrientation = true;
    /**
     * Whether to render image previews.
     */
    public bool $allowImagePreview = true;
    /**
     * Whether to apply crop, resize, and output transforms on the client before submission.
     */
    public bool $allowImageTransform = false;
    /**
     * Whether to accept multiple files; the input name receives the `[]` suffix.
     */
    public bool $allowMultiple = false;
    /**
     * Whether to render PDF previews.
     */
    public bool $allowPdfPreview = false;
    /**
     * Whether to load FilePond, its plugins, and Cropper.js from the CDN instead of publishing them.
     */
    public bool $cdn = false;
    /**
     * @var array<string, mixed> FilePond options merged last, overriding typed properties and localized labels.
     */
    public array $config = [];
    /**
     * @var array<string, mixed> Cropper.js editor options: `aspectRatio`, `aspectRatios`, `labels`, and `options`.
     */
    public array $cropper = [];
    /**
     * @var list<mixed> Initial FilePond `files`, either sources or `{source, options}` entries.
     */
    public array $files = [];
    /**
     * @var string|null Crop aspect ratio in `width:height` format, for example `1:1`.
     */
    public string|null $imageCropAspectRatio = null;
    /**
     * @var string|null Drop area label, or `null` to use the localized default.
     */
    public string|null $labelIdle = null;
    /**
     * @var int|null Maximum number of files, or `null` for no limit.
     */
    public int|null $maxFiles = null;
    /**
     * @var int|string|null Maximum file size in bytes or as a FilePond size string such as `2MB`.
     */
    public int|string|null $maxFileSize = null;
    /**
     * @var int|string|null Maximum total size of all files in bytes or as a FilePond size string.
     */
    public int|string|null $maxTotalFileSize = null;
    /**
     * @var int|string|null Minimum file size in bytes or as a FilePond size string.
     */
    public int|string|null $minFileSize = null;
    /**
     * Whether the input is required for form submission.
     */
    public bool $required = false;

    /**
     * Input ID used for the FilePond instance.
     */
    private string $inputId = '';

    /**
     * Returns the FilePond options passed to `FilePond.create()`.
     *
     * @return array<string, mixed> Localized labels, typed properties, and {@see $config} merged in that order.
     */
    public function getOptions(): array
    {
        $typed = [
            'acceptedFileTypes' => $this->acceptedFileTypes === [] ? null : $this->acceptedFileTypes,
            'allowFileEncode' => $this->allowFileEncode,
            'allowFilePoster' => $this->allowFilePoster,
            'allowFileRename' => $this->allowFileRename,
            'allowFileSizeValidation' => $this->allowFileSizeValidation,
            'allowFileTypeValidation' => $this->allowFileTypeValidation,
            'allowImageCrop' => $this->allowImageCrop,
            'allowImageEdit' => $this->allowImageEdit,
            'allowImageExifOrientation' => $this->allowImageExifOrientation,
            'allowImagePreview' => $this->allowImagePreview,
            'allowImageTransform' => $this->allowImageTransform,
            'allowMultiple' => $this->allowMultiple,
            'allowPdfPreview' => $this->allowPdfPreview,
            'files' => $this->files === [] ? null : $this->files,
            'imageCropAspectRatio' => $this->imageCropAspectRatio,
            'labelIdle' => $this->labelIdle,
            'maxFiles' => $this->maxFiles,
            'maxFileSize' => $this->maxFileSize,
            'maxTotalFileSize' => $this->maxTotalFileSize,
            'minFileSize' => $this->minFileSize,
            'required' => $this->required,
        ];

        $options = [
            ...array_filter($typed, static fn(mixed $value): bool => $value !== null),
            ...$this->config,
        ];

        $options = [...$this->getLabels($options), ...$options];

        if (self::isEnabled($options, 'allowImageEdit') && array_key_exists('imageEditEditor', $options) === false) {
            $cropperOptions = $this->getCropperOptions($options['imageCropAspectRatio'] ?? null);

            $options['imageEditEditor'] = new JsExpression(
                'yii2FilePond.cropper.createEditor(' . Json::htmlEncode($cropperOptions) . ')',
            );
        }

        return $options;
    }

    /**
     * Returns the plugins enabled by the effective options.
     *
     * @return list<Plugin> Enabled plugins in registration order.
     */
    public function getPlugins(): array
    {
        return self::filterPlugins($this->getOptions());
    }

    /**
     * @throws InvalidConfigException if {@see $imageCropAspectRatio} or {@see $cropper} is misconfigured.
     */
    public function init(): void
    {
        parent::init();

        $id = $this->options['id'] ?? null;

        $this->inputId = is_string($id) ? $id : ($this->getId() ?? '');

        if ($this->imageCropAspectRatio !== null) {
            self::assertAspectRatio($this->imageCropAspectRatio);
        }

        if ($this->cropper !== [] && self::isEnabled($this->getOptions(), 'allowImageEdit') === false) {
            throw new InvalidConfigException(
                Message::CROPPER_REQUIRES_IMAGE_EDIT->getMessage(),
            );
        }
    }

    public function run(): string
    {
        $options = $this->getOptions();

        $this->registerAssets($options);

        return $this->renderInput($options);
    }

    /**
     * @throws InvalidConfigException if the value is not a `width:height` pair of positive numbers.
     */
    private static function assertAspectRatio(string $value): void
    {
        if (
            preg_match('/^(\d+(?:\.\d+)?):(\d+(?:\.\d+)?)$/', $value, $matches) !== 1
            || $matches[1] <= 0
            || $matches[2] <= 0
        ) {
            throw new InvalidConfigException(
                Message::INVALID_CROP_ASPECT_RATIO->getMessage($value),
            );
        }
    }

    /**
     * @param array<string, mixed> $options Effective FilePond options.
     *
     * @return list<Plugin> Plugins whose option is `true`, in registration order.
     */
    private static function filterPlugins(array $options): array
    {
        return array_values(
            array_filter(
                Plugin::cases(),
                static fn(Plugin $plugin): bool => self::isEnabled($options, $plugin->option()),
            ),
        );
    }

    /**
     * @param mixed $aspectRatio Effective `imageCropAspectRatio`, used as the initial editor ratio.
     *
     * @return array<string, mixed> Editor options with localized labels.
     */
    private function getCropperOptions(mixed $aspectRatio): array
    {
        $labels = [
            'apply' => Yii::t(self::TRANSLATION_CATEGORY, 'Apply'),
            'cancel' => Yii::t(self::TRANSLATION_CATEGORY, 'Cancel'),
            'free' => Yii::t(self::TRANSLATION_CATEGORY, 'Free'),
            'reset' => Yii::t(self::TRANSLATION_CATEGORY, 'Reset'),
            'title' => Yii::t(self::TRANSLATION_CATEGORY, 'Edit image'),
            'zoomIn' => Yii::t(self::TRANSLATION_CATEGORY, 'Zoom in'),
            'zoomOut' => Yii::t(self::TRANSLATION_CATEGORY, 'Zoom out'),
        ];

        $custom = $this->cropper['labels'] ?? [];

        $options = array_replace(
            [
                'aspectRatio' => $aspectRatio,
                'aspectRatios' => ['free', '1:1', '16:9', '4:3', '3:2'],
                'options' => [],
            ],
            $this->cropper,
        );

        $options['labels'] = array_replace($labels, is_array($custom) ? $custom : []);

        return $options;
    }

    private function getInputName(): string
    {
        if ($this->model instanceof Model && is_string($this->attribute)) {
            return Html::getInputName($this->model, $this->attribute);
        }

        return is_string($this->name) ? $this->name : '';
    }

    /**
     * @param array<string, mixed> $options Typed properties merged with {@see $config}.
     *
     * @return array<string, string> Localized labels for the core and the enabled validation plugins.
     */
    private function getLabels(array $options): array
    {
        $labels = [
            'labelIdle' => Yii::t(
                self::TRANSLATION_CATEGORY,
                'Drag & Drop your files or <span class="filepond--label-action"> Browse </span>',
            ),
        ];

        if (self::isEnabled($options, 'allowFileSizeValidation')) {
            $labels += [
                'labelMaxFileSize' => Yii::t(self::TRANSLATION_CATEGORY, 'Maximum file size is {filesize}'),
                'labelMaxFileSizeExceeded' => Yii::t(self::TRANSLATION_CATEGORY, 'File is too large'),
                'labelMaxTotalFileSize' => Yii::t(self::TRANSLATION_CATEGORY, 'Maximum total file size is {filesize}'),
                'labelMaxTotalFileSizeExceeded' => Yii::t(self::TRANSLATION_CATEGORY, 'Maximum total size exceeded'),
                'labelMinFileSize' => Yii::t(self::TRANSLATION_CATEGORY, 'Minimum file size is {filesize}'),
                'labelMinFileSizeExceeded' => Yii::t(self::TRANSLATION_CATEGORY, 'File is too small'),
            ];
        }

        if (self::isEnabled($options, 'allowFileTypeValidation')) {
            $labels += [
                'fileValidateTypeLabelExpectedTypes' => Yii::t(
                    self::TRANSLATION_CATEGORY,
                    'Expects {allButLastType} or {lastType}',
                ),
                'labelFileTypeNotAllowed' => Yii::t(self::TRANSLATION_CATEGORY, 'File type not allowed'),
            ];
        }

        return $labels;
    }

    /**
     * @param array<string, mixed> $options Effective FilePond options.
     * @param list<Plugin> $plugins Plugins enabled by the options.
     */
    private function getScript(array $options, array $plugins): string
    {
        $names = array_map(static fn(Plugin $plugin): string => $plugin->value, $plugins);

        return 'yii2FilePond.create('
            . Json::htmlEncode($this->inputId) . ', '
            . Json::htmlEncode($names) . ', '
            . Json::htmlEncode($options)
            . ');';
    }

    /**
     * @param array<string, mixed> $options Effective FilePond options.
     */
    private static function isEnabled(array $options, string $option): bool
    {
        return ($options[$option] ?? null) === true;
    }

    /**
     * @param array<string, mixed> $options Effective FilePond options.
     */
    private function registerAssets(array $options): void
    {
        $view = $this->getView();

        $plugins = self::filterPlugins($options);

        FilePondAsset::registerWith($view, $this->cdn);

        foreach ($plugins as $plugin) {
            $plugin->assetClass()::registerWith($view, $this->cdn);
        }

        FilePondWidgetAsset::register($view);

        if (self::isEnabled($options, 'allowImageEdit')) {
            CropperAsset::registerWith($view, $this->cdn);
            FilePondCropperAsset::register($view);
        }

        $view->registerJs($this->getScript($options, $plugins), View::POS_END);
    }

    /**
     * @param array<string, mixed> $options Effective FilePond options.
     */
    private function renderInput(array $options): string
    {
        $attributes = $this->options;

        $class = $attributes['class'] ?? '';

        $classes = preg_split('/\s+/', is_string($class) ? $class : '', -1, PREG_SPLIT_NO_EMPTY);

        unset(
            $attributes['class'],
            $attributes['id'],
            $attributes['multiple'],
            $attributes['name'],
            $attributes['placeholder'],
            $attributes['required'],
            $attributes['value'],
        );

        CSSClass::add($attributes, [...array_diff($classes === false ? [] : $classes, ['form-control']), 'filepond']);

        return InputFile::tag()
            ->attributes($attributes)
            ->id($this->inputId)
            ->name($this->getInputName())
            ->multiple(self::isEnabled($options, 'allowMultiple') ? true : null)
            ->required(self::isEnabled($options, 'required') ? true : null)
            ->render();
    }
}

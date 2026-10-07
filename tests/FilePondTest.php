<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests;

use PHPUnit\Framework\Attributes\Group;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\web\{JsExpression, View};
use yii2\extensions\filepond\asset\{CropperAsset, FilePondAsset, FilePondCropperAsset, FilePondWidgetAsset, Plugin};
use yii2\extensions\filepond\asset\plugin\{
    FileEncodeAsset,
    FileValidateSizeAsset,
    FileValidateTypeAsset,
    ImageEditAsset,
    ImageExifOrientationAsset,
    ImagePreviewAsset,
    PdfPreviewAsset,
};
use yii2\extensions\filepond\exception\Message;
use yii2\extensions\filepond\FilePond;
use yii2\extensions\filepond\tests\support\stub\TestForm;
use yii2\extensions\filepond\tests\support\TestCase;

use function array_keys;
use function array_map;
use function end;
use function implode;
use function is_array;
use function is_string;
use function json_decode;
use function preg_match;
use function strpos;
use function substr;

/**
 * Unit tests for {@see FilePond} rendering, option assembly, plugin registration, and script generation.
 */
#[Group('widget')]
final class FilePondTest extends TestCase
{
    public function testAllowMultipleAppendsArraySuffixAndMultipleAttribute(): void
    {
        $this->mockWebApplication();

        $html = FilePond::widget(['name' => 'documents', 'allowMultiple' => true, 'view' => $this->view()]);

        self::assertStringContainsString(
            'name="documents[]"',
            $html,
            'Name must receive the array suffix.',
        );
        self::assertStringContainsString(
            'multiple',
            $html,
            'Input must carry the `multiple` attribute.',
        );
    }

    public function testAssetsFollowEnabledPlugins(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(['name' => 'file', 'allowPdfPreview' => true, 'view' => $view]);

        $bundles = array_keys($view->assetBundles);

        self::assertContains(
            FilePondAsset::class,
            $bundles,
            'Core bundle must be registered.',
        );
        self::assertContains(
            FilePondWidgetAsset::class,
            $bundles,
            'Widget runtime bundle must be registered.',
        );
        self::assertContains(
            FileEncodeAsset::class,
            $bundles,
            'Default File Encode bundle must be registered.',
        );
        self::assertContains(
            FileValidateSizeAsset::class,
            $bundles,
            'Default size validation bundle expected.',
        );
        self::assertContains(
            FileValidateTypeAsset::class,
            $bundles,
            'Default type validation bundle expected.',
        );
        self::assertContains(
            ImageExifOrientationAsset::class,
            $bundles,
            'Default EXIF bundle expected.',
        );
        self::assertContains(
            ImagePreviewAsset::class,
            $bundles,
            'Default preview bundle expected.',
        );
        self::assertContains(
            PdfPreviewAsset::class,
            $bundles,
            'Enabled PDF preview bundle expected.',
        );
        self::assertNotContains(
            ImageEditAsset::class,
            $bundles,
            'Disabled plugins must not register bundles.',
        );
        self::assertNotContains(
            CropperAsset::class,
            $bundles,
            'Cropper.js must not load without image editing.',
        );
        self::assertNotContains(
            FilePondCropperAsset::class,
            $bundles,
            'Editor adapter must not load without editing.',
        );
    }

    public function testCdnAppliesToCoreWithoutPlugins(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(
            [
                'name' => 'file',
                'allowFileEncode' => false,
                'allowFileSizeValidation' => false,
                'allowFileTypeValidation' => false,
                'allowImageExifOrientation' => false,
                'allowImagePreview' => false,
                'cdn' => true,
                'view' => $view,
            ],
        );

        $core = $view->assetBundles[FilePondAsset::class] ?? null;

        self::assertInstanceOf(
            FilePondAsset::class,
            $core,
            'Core bundle must be registered without plugins.',
        );
        self::assertTrue(
            $core->cdn,
            'Core bundle must use the CDN without plugin dependencies.',
        );
    }

    public function testCdnPropagatesToRegisteredBundles(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(['name' => 'file', 'allowImageEdit' => true, 'cdn' => true, 'view' => $view]);

        $core = $view->assetBundles[FilePondAsset::class] ?? null;
        $preview = $view->assetBundles[ImagePreviewAsset::class] ?? null;
        $cropper = $view->assetBundles[CropperAsset::class] ?? null;

        self::assertInstanceOf(
            FilePondAsset::class,
            $core,
            'Core bundle must be registered.',
        );
        self::assertInstanceOf(
            ImagePreviewAsset::class,
            $preview,
            'Preview bundle must be registered.',
        );
        self::assertInstanceOf(
            CropperAsset::class,
            $cropper,
            'Cropper.js bundle must be registered.',
        );
        self::assertTrue(
            $core->cdn,
            'Core bundle must use the CDN.',
        );
        self::assertTrue(
            $preview->cdn,
            'Plugin bundle must inherit the CDN mode.',
        );
        self::assertTrue(
            $cropper->cdn,
            'Cropper.js bundle must use the CDN.',
        );
        self::assertStringStartsWith(
            'https://unpkg.com/filepond@',
            self::url($core->js),
            'Script must point to the CDN.',
        );
    }

    public function testConfigAllowMultipleControlsInputMarkup(): void
    {
        $this->mockWebApplication();

        $enabled = FilePond::widget(
            ['name' => 'documents', 'config' => ['allowMultiple' => true], 'view' => $this->view()],
        );
        $disabled = FilePond::widget(
            [
                'name' => 'documents',
                'allowMultiple' => true,
                'config' => ['allowMultiple' => false],
                'view' => $this->view(),
            ],
        );

        self::assertStringContainsString(
            'name="documents[]"',
            $enabled,
            'Enabling through config must add the array suffix.',
        );
        self::assertStringContainsString(
            'multiple',
            $enabled,
            'Enabling through config must add the `multiple` attribute.',
        );
        self::assertStringContainsString(
            'name="documents"',
            $disabled,
            'Disabling through config must drop the array suffix.',
        );
        self::assertStringNotContainsString(
            'multiple',
            $disabled,
            'Disabling through config must drop the `multiple` attribute.',
        );
    }

    public function testConfigCanDisableImageEditAssets(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(
            ['name' => 'file', 'allowImageEdit' => true, 'config' => ['allowImageEdit' => false], 'view' => $view],
        );

        $bundles = array_keys($view->assetBundles);

        self::assertStringNotContainsString(
            'createEditor',
            self::script($view),
            'Editor must not be injected.',
        );
        self::assertNotContains(
            CropperAsset::class,
            $bundles,
            'Cropper.js must not load.',
        );
        self::assertNotContains(
            FilePondCropperAsset::class,
            $bundles,
            'Editor adapter must not load.',
        );
    }

    public function testConfigImageEditLoadsCropperAssetsAndOptions(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(
            [
                'name' => 'file',
                'config' => ['allowImageEdit' => true, 'imageCropAspectRatio' => '16:9'],
                'cropper' => ['aspectRatios' => ['16:9']],
                'view' => $view,
            ],
        );

        $script = self::script($view);

        $bundles = array_keys($view->assetBundles);

        self::assertStringContainsString(
            'yii2FilePond.cropper.createEditor({"aspectRatio":"16:9","aspectRatios":["16:9"]',
            $script,
            'Editor must receive the effective ratio and the cropper presets.',
        );
        self::assertContains(
            CropperAsset::class,
            $bundles,
            'Cropper.js bundle must be registered.',
        );
        self::assertContains(
            FilePondCropperAsset::class,
            $bundles,
            'Editor adapter bundle must be registered.',
        );
    }

    public function testConfigOverridesTypedPropertiesAndLabels(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(
            [
                'name' => 'file',
                'maxFiles' => 2,
                'config' => ['maxFiles' => 5, 'labelIdle' => 'Drop here', 'storeAsFile' => true],
                'view' => $view,
            ],
        );

        $options = self::decodeOptions($view);

        self::assertSame(
            5,
            $options['maxFiles'] ?? null,
            'Config value must win over the typed property.',
        );
        self::assertSame(
            'Drop here',
            $options['labelIdle'] ?? null,
            'Config value must win over the localized label.',
        );
        self::assertTrue(
            $options['storeAsFile'] ?? null,
            'Arbitrary FilePond options must pass through.',
        );
    }

    public function testConfigRequiredControlsInputAttribute(): void
    {
        $this->mockWebApplication();

        $enabled = FilePond::widget(['name' => 'file', 'config' => ['required' => true], 'view' => $this->view()]);
        $disabled = FilePond::widget(
            ['name' => 'file', 'required' => true, 'config' => ['required' => false], 'view' => $this->view()],
        );

        self::assertStringContainsString(
            ' required',
            $enabled,
            'Enabling through config must add the `required` attribute.',
        );
        self::assertStringNotContainsString(
            ' required',
            $disabled,
            'Disabling through config must drop the `required` attribute.',
        );
    }

    public function testConfigValidationFlagsControlLabels(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(
            [
                'name' => 'file',
                'allowFileSizeValidation' => false,
                'config' => ['allowFileSizeValidation' => true, 'allowFileTypeValidation' => false],
                'view' => $view,
            ],
        );

        $options = self::decodeOptions($view);

        self::assertSame(
            'Maximum file size is {filesize}',
            $options['labelMaxFileSize'] ?? null,
            'Size labels must follow the config flag.',
        );
        self::assertArrayNotHasKey(
            'labelFileTypeNotAllowed',
            $options,
            'Type labels must follow the config flag.',
        );
    }

    public function testCropperOptionsMatchDefaultsExactly(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(['name' => 'file', 'allowImageEdit' => true, 'imageCropAspectRatio' => '1:1', 'view' => $view]);

        $expected = Json::htmlEncode(
            [
                'aspectRatio' => '1:1',
                'aspectRatios' => ['free', '1:1', '16:9', '4:3', '3:2'],
                'options' => [],
                'labels' => [
                    'apply' => 'Apply',
                    'cancel' => 'Cancel',
                    'free' => 'Free',
                    'reset' => 'Reset',
                    'title' => 'Edit image',
                    'zoomIn' => 'Zoom in',
                    'zoomOut' => 'Zoom out',
                ],
            ],
        );

        self::assertStringContainsString(
            '"imageEditEditor":yii2FilePond.cropper.createEditor(' . $expected . ')',
            self::script($view),
            'Editor must receive the default presets and labels.',
        );
    }

    public function testCropperOptionsMergeCustomLabelsAndAspectRatios(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(
            [
                'name' => 'file',
                'allowImageEdit' => true,
                'imageCropAspectRatio' => '4:3',
                'cropper' => [
                    'aspectRatios' => ['free', '4:3'],
                    'labels' => ['apply' => 'Crop'],
                    'options' => ['container' => '#editor'],
                ],
                'view' => $view,
            ],
        );

        $script = self::script($view);

        self::assertStringContainsString(
            '"aspectRatio":"4:3"',
            $script,
            'Crop aspect ratio must seed the editor.',
        );
        self::assertStringContainsString(
            '"aspectRatios":["free","4:3"]',
            $script,
            'Custom presets must be kept.',
        );
        self::assertStringContainsString(
            '"apply":"Crop"',
            $script,
            'Custom label must override the default.',
        );
        self::assertStringContainsString(
            '"cancel":"Cancel"',
            $script,
            'Default labels must be preserved.',
        );
        self::assertStringContainsString(
            '"container":"#editor"',
            $script,
            'Cropper.js options must pass through.',
        );
    }

    public function testFilesAndAcceptedFileTypesAreEmittedWhenProvided(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(
            [
                'name' => 'file',
                'acceptedFileTypes' => ['image/*'],
                'files' => [['source' => '/uploads/logo.png', 'options' => ['type' => 'local']]],
                'view' => $view,
            ],
        );

        $options = self::decodeOptions($view);

        self::assertSame(
            ['image/*'],
            $options['acceptedFileTypes'] ?? null,
            'Accepted types must be emitted.',
        );
        self::assertSame(
            [['source' => '/uploads/logo.png', 'options' => ['type' => 'local']]],
            $options['files'] ?? null,
            'Initial files must be emitted.',
        );
    }

    public function testImageEditEditorFromConfigIsPreserved(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(
            [
                'name' => 'file',
                'allowImageEdit' => true,
                'config' => ['imageEditEditor' => new JsExpression('window.customEditor')],
                'view' => $view,
            ],
        );

        $script = self::script($view);

        self::assertStringContainsString(
            '"imageEditEditor":window.customEditor',
            $script,
            'Custom editor expected.',
        );
        self::assertStringNotContainsString(
            'createEditor',
            $script,
            'Default editor must not be injected.',
        );
    }

    public function testImageEditInjectsEditorExpressionAndAssets(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(['name' => 'file', 'allowImageEdit' => true, 'view' => $view]);

        $script = self::script($view);

        $bundles = array_keys($view->assetBundles);

        self::assertStringContainsString(
            '"imageEditEditor":yii2FilePond.cropper.createEditor({',
            $script,
            'Editor expression must be emitted raw.',
        );
        self::assertStringContainsString(
            '"title":"Edit image"',
            $script,
            'Editor labels must be localized.',
        );
        self::assertStringContainsString(
            '"FilePondPluginImageEdit"',
            $script,
            'Image Edit plugin must register.',
        );
        self::assertContains(
            ImageEditAsset::class,
            $bundles,
            'Image Edit bundle must be registered.',
        );
        self::assertContains(
            CropperAsset::class,
            $bundles,
            'Cropper.js bundle must be registered.',
        );
        self::assertContains(
            FilePondCropperAsset::class,
            $bundles,
            'Editor adapter bundle must be registered.',
        );
    }

    public function testLabelsAreTranslated(): void
    {
        $this->mockWebApplication(['language' => 'es']);

        $view = $this->view();

        FilePond::widget(['name' => 'file', 'view' => $view]);

        $options = self::decodeOptions($view);

        self::assertSame(
            'Tipo de archivo no permitido',
            $options['labelFileTypeNotAllowed'] ?? null,
            'Label must be Spanish.',
        );
        self::assertSame(
            'El tamaño máximo del archivo es {filesize}',
            $options['labelMaxFileSize'] ?? null,
            'Placeholders must survive translation.',
        );
    }

    public function testLabelsFollowEnabledValidationPlugins(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(
            [
                'name' => 'file',
                'allowFileSizeValidation' => false,
                'allowFileTypeValidation' => false,
                'view' => $view,
            ],
        );

        $options = self::decodeOptions($view);

        self::assertArrayHasKey(
            'labelIdle',
            $options,
            'Core label must always be present.',
        );
        self::assertArrayNotHasKey(
            'labelMaxFileSize',
            $options,
            'Size labels require the size plugin.',
        );
        self::assertArrayNotHasKey(
            'labelFileTypeNotAllowed',
            $options,
            'Type labels require the type plugin.',
        );
    }

    public function testModelWithoutAttributeFallsBackToName(): void
    {
        $this->mockWebApplication();

        $html = FilePond::widget(['model' => new TestForm(), 'name' => 'custom', 'view' => $this->view()]);

        self::assertStringContainsString(
            'name="custom"',
            $html,
            'Name must be used without an attribute.',
        );
    }

    public function testNonStringOptionIdFallsBackToWidgetId(): void
    {
        $this->mockWebApplication();

        $html = FilePond::widget(['name' => 'file', 'options' => ['id' => 42], 'view' => $this->view()]);

        self::assertStringContainsString(
            'id="w0"',
            $html,
            'Widget id must be used when the option is not a string.',
        );
    }

    public function testOptionsEmitEveryTypedValue(): void
    {
        $this->mockWebApplication();

        $widget = new FilePond(
            [
                'name' => 'file',
                'acceptedFileTypes' => ['image/png'],
                'allowMultiple' => true,
                'files' => ['/a.png'],
                'imageCropAspectRatio' => '3:2',
                'labelIdle' => 'Drop',
                'maxFiles' => 4,
                'maxFileSize' => '1MB',
                'maxTotalFileSize' => 4096,
                'minFileSize' => '1KB',
                'required' => true,
                'view' => $this->view(),
            ],
        );

        $options = $widget->getOptions();

        self::assertSame(
            ['image/png'],
            $options['acceptedFileTypes'] ?? null,
            'Accepted types expected.',
        );
        self::assertTrue(
            $options['allowMultiple'] ?? null,
            'Multiple flag expected.',
        );
        self::assertSame(
            ['/a.png'],
            $options['files'] ?? null,
            'Files expected.',
        );
        self::assertSame(
            '3:2',
            $options['imageCropAspectRatio'] ?? null,
            'Aspect ratio expected.',
        );
        self::assertSame(
            'Drop',
            $options['labelIdle'] ?? null,
            'Custom idle label expected.',
        );
        self::assertSame(
            4,
            $options['maxFiles'] ?? null,
            'Max files expected.',
        );
        self::assertSame(
            '1MB',
            $options['maxFileSize'] ?? null,
            'Max file size expected.',
        );
        self::assertSame(
            4096,
            $options['maxTotalFileSize'] ?? null,
            'Max total size expected.',
        );
        self::assertSame(
            '1KB',
            $options['minFileSize'] ?? null,
            'Min file size expected.',
        );
        self::assertTrue(
            $options['required'] ?? null,
            'Required flag expected.',
        );
    }

    public function testOptionsMatchDefaultsExactly(): void
    {
        $this->mockWebApplication();

        $widget = new FilePond(['name' => 'file', 'maxFiles' => 2, 'view' => $this->view()]);

        self::assertSame(
            [
                'labelIdle' => 'Drag & Drop your files or <span class="filepond--label-action"> Browse </span>',
                'labelMaxFileSize' => 'Maximum file size is {filesize}',
                'labelMaxFileSizeExceeded' => 'File is too large',
                'labelMaxTotalFileSize' => 'Maximum total file size is {filesize}',
                'labelMaxTotalFileSizeExceeded' => 'Maximum total size exceeded',
                'labelMinFileSize' => 'Minimum file size is {filesize}',
                'labelMinFileSizeExceeded' => 'File is too small',
                'fileValidateTypeLabelExpectedTypes' => 'Expects {allButLastType} or {lastType}',
                'labelFileTypeNotAllowed' => 'File type not allowed',
                'allowFileEncode' => true,
                'allowFilePoster' => false,
                'allowFileRename' => false,
                'allowFileSizeValidation' => true,
                'allowFileTypeValidation' => true,
                'allowImageCrop' => false,
                'allowImageEdit' => false,
                'allowImageExifOrientation' => true,
                'allowImagePreview' => true,
                'allowImageTransform' => false,
                'allowMultiple' => false,
                'allowPdfPreview' => false,
                'maxFiles' => 2,
                'required' => false,
            ],
            $widget->getOptions(),
            'Default options must contain the labels, the plugin switches, and the typed values in order.',
        );
    }

    public function testOptionsOmitNullAndEmptyTypedValues(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(['name' => 'file', 'view' => $view]);

        $options = self::decodeOptions($view);

        self::assertArrayNotHasKey(
            'acceptedFileTypes',
            $options,
            'Empty accepted types must be omitted.',
        );
        self::assertArrayNotHasKey(
            'files',
            $options,
            'Empty files must be omitted.',
        );
        self::assertArrayNotHasKey(
            'maxFiles',
            $options,
            '`null` values must be omitted.',
        );
        self::assertArrayNotHasKey(
            'imageCropAspectRatio',
            $options,
            '`null` values must be omitted.',
        );
        self::assertFalse(
            $options['required'] ?? null,
            'Boolean defaults must be emitted.',
        );
    }

    public function testPluginsFollowEffectiveOptions(): void
    {
        $this->mockWebApplication();

        $widget = new FilePond(
            [
                'name' => 'file',
                'allowImagePreview' => true,
                'allowPdfPreview' => true,
                'config' => ['allowImagePreview' => false],
                'view' => $this->view(),
            ],
        );

        $plugins = $widget->getPlugins();

        self::assertContains(
            Plugin::PDF_PREVIEW,
            $plugins,
            'Enabled plugin must be listed.',
        );
        self::assertNotContains(
            Plugin::IMAGE_PREVIEW,
            $plugins,
            'Config must be able to disable a plugin.',
        );
        self::assertSame(
            [
                Plugin::FILE_ENCODE,
                Plugin::FILE_VALIDATE_SIZE,
                Plugin::FILE_VALIDATE_TYPE,
                Plugin::IMAGE_EXIF_ORIENTATION,
                Plugin::PDF_PREVIEW,
            ],
            $plugins,
            'Plugins must follow the enum order.',
        );
    }

    public function testPluginsRequireStrictBooleanOptions(): void
    {
        $this->mockWebApplication();

        $widget = new FilePond(['name' => 'file', 'config' => ['allowPdfPreview' => 1], 'view' => $this->view()]);

        self::assertNotContains(
            Plugin::PDF_PREVIEW,
            $widget->getPlugins(),
            'Truthy values must not enable plugins.',
        );
    }

    public function testRemovesFormControlClassAndKeepsOtherClasses(): void
    {
        $this->mockWebApplication();

        $html = FilePond::widget(
            [
                'name' => 'file',
                'options' => ['class' => 'form-control custom', 'data-role' => 'upload', 'placeholder' => 'x'],
                'view' => $this->view(),
            ],
        );

        self::assertStringContainsString(
            'class="custom filepond"',
            $html,
            'Bootstrap class must be removed.',
        );
        self::assertStringContainsString(
            'data-role="upload"',
            $html,
            'Other attributes must be preserved.',
        );
        self::assertStringNotContainsString(
            'placeholder',
            $html,
            'Placeholder is not valid for file inputs.',
        );
    }

    public function testRenderInputWithModel(): void
    {
        $this->mockWebApplication();

        $html = FilePond::widget(['model' => new TestForm(), 'attribute' => 'avatar', 'view' => $this->view()]);

        self::assertStringContainsString(
            'id="testform-avatar"',
            $html,
            'Id must derive from the model.',
        );
        self::assertStringContainsString(
            'name="TestForm[avatar]"',
            $html,
            'Name must derive from the model.',
        );
    }

    public function testRenderInputWithName(): void
    {
        $this->mockWebApplication();

        $html = FilePond::widget(['name' => 'avatar', 'view' => $this->view()]);

        self::assertSame(
            '<input class="filepond" id="w0" name="avatar" type="file">',
            $html,
            'Input must render with the widget class, id, name, and type.',
        );
    }

    public function testRequiredAddsAttributeAndOption(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        $html = FilePond::widget(['name' => 'file', 'required' => true, 'view' => $view]);

        self::assertStringContainsString(
            'required',
            $html,
            'Input must carry the `required` attribute.',
        );
        self::assertTrue(
            self::decodeOptions($view)['required'] ?? null,
            'Option must be enabled.',
        );
    }

    public function testScriptIsRegisteredAtEndPositionWithJsonOptions(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(['name' => 'file', 'view' => $view]);

        $script = self::script($view);

        self::assertArrayHasKey(
            View::POS_END,
            $view->js,
            'Script must be registered at `POS_END`.',
        );
        self::assertStringStartsWith(
            'yii2FilePond.create("w0", ["FilePondPluginFileEncode","FilePondPluginFileValidateSize",'
            . '"FilePondPluginFileValidateType","FilePondPluginImageExifOrientation","FilePondPluginImagePreview"], {',
            $script,
            'Runtime call must target the input and list the plugin globals as strings.',
        );
        self::assertStringEndsWith(
            '});',
            $script,
            'Options object must close the call.',
        );
    }

    public function testThrowInvalidConfigExceptionForAspectRatioWithLeadingText(): void
    {
        $this->mockWebApplication();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            Message::INVALID_CROP_ASPECT_RATIO->getMessage('x1:1'),
        );

        new FilePond(['name' => 'file', 'imageCropAspectRatio' => 'x1:1']);
    }

    public function testThrowInvalidConfigExceptionForAspectRatioWithTrailingText(): void
    {
        $this->mockWebApplication();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            Message::INVALID_CROP_ASPECT_RATIO->getMessage('1:1x'),
        );

        new FilePond(['name' => 'file', 'imageCropAspectRatio' => '1:1x']);
    }

    public function testThrowInvalidConfigExceptionForMalformedAspectRatio(): void
    {
        $this->mockWebApplication();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            Message::INVALID_CROP_ASPECT_RATIO->getMessage('square'),
        );

        new FilePond(['name' => 'file', 'imageCropAspectRatio' => 'square']);
    }

    public function testThrowInvalidConfigExceptionForZeroAspectRatio(): void
    {
        $this->mockWebApplication();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            Message::INVALID_CROP_ASPECT_RATIO->getMessage('0:1'),
        );

        new FilePond(['name' => 'file', 'imageCropAspectRatio' => '0:1']);
    }

    public function testThrowInvalidConfigExceptionForZeroHeightAspectRatio(): void
    {
        $this->mockWebApplication();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            Message::INVALID_CROP_ASPECT_RATIO->getMessage('1:0.0'),
        );

        new FilePond(['name' => 'file', 'imageCropAspectRatio' => '1:0.0']);
    }

    public function testThrowInvalidConfigExceptionWhenCropperOptionsWithoutImageEdit(): void
    {
        $this->mockWebApplication();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            Message::CROPPER_REQUIRES_IMAGE_EDIT->getMessage(),
        );

        new FilePond(['name' => 'file', 'cropper' => ['aspectRatios' => ['1:1']]]);
    }

    public function testTwoWidgetsRegisterIndependentScripts(): void
    {
        $this->mockWebApplication();

        $view = $this->view();

        FilePond::widget(['name' => 'first', 'maxFiles' => 1, 'view' => $view]);
        FilePond::widget(['name' => 'second', 'maxFiles' => 3, 'view' => $view]);

        $registered = $view->js[View::POS_END] ?? [];

        $scripts = implode(
            "\n",
            array_map(static fn(mixed $script): string => is_string($script)
                ? $script : '', is_array($registered) ? $registered : []),
        );

        self::assertStringContainsString(
            'yii2FilePond.create("w0"',
            $scripts,
            'First widget script expected.',
        );
        self::assertStringContainsString(
            'yii2FilePond.create("w1"',
            $scripts,
            'Second widget script expected.',
        );
        self::assertSame(
            1,
            preg_match('/"maxFiles":1/', $scripts),
            'First options must be isolated.',
        );
        self::assertSame(
            1,
            preg_match('/"maxFiles":3/', $scripts),
            'Second options must be isolated.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function decodeOptions(View $view): array
    {
        $script = self::script($view);

        $json = substr($script, (int) strpos($script, ', {') + 2, -2);

        /** @var array<string, mixed> */
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    private static function script(View $view): string
    {
        $registered = $view->js[View::POS_END] ?? [];

        $script = is_array($registered) ? end($registered) : false;

        return is_string($script) ? $script : '';
    }

    /**
     * @param array<array-key, mixed> $files
     */
    private static function url(array $files): string
    {
        $url = $files[0] ?? null;

        self::assertIsString(
            $url,
            'First asset file must be a string.',
        );

        return $url;
    }
}

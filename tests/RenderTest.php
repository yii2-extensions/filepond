<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Tests;

use Yii;
use Yii2\Extensions\FilePond\FilePond;
use Yii2\Extensions\FilePond\Tests\Support\TestForm;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class RenderTest extends TestCase
{
    public function setup(): void
    {
        parent::setUp();
        $this->mockApplication();

        FilePond::$counter = 0;

        $this->view = Yii::$app->getView();
    }

    public function testAllowMultiple(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'allowMultiple' => true,
                'model' => new TestForm(),
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file" multiple>',
            $filePond,
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('"allowMultiple":true', $result);
    }

    public function testCssClass(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'cssClass' => 'test-class',
                'model' => new TestForm(),
            ],
        );

        $this->assertSame(
            '<input class="test-class filepond" id="testform-array" name="TestForm[array][]" type="file">',
            $filePond,
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('"className":"test-class"', $result);
    }

    public function testConfig(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'config' => ['forceRevert' => true, 'storeAsFile' => true],
                'model' => new TestForm(),
            ],
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('"forceRevert":true,"storeAsFile":true', $result);
    }

    public function testFileRename(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'fileRename' => <<<JS
                    functionFileRename() {
                        return 'my_new_name.jpg';
                    }
                JS,
                'model' => new TestForm(),
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file">',
            $filePond,
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('functionFileRename()', $result);

        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'fileRename' => <<<JS
                    functionFileRename() {
                        return 'my_new_name.jpg';
                    }
                JS,
                'fileValidateTypeDetectType' => <<<JS
                    fileValidateTypeDetectType: (source, type) =>
                        new Promise((resolve, reject) => {
                        // Do custom type detection here and return with promise
                        resolve(type);
                    }),
                JS,
                'model' => new TestForm(),
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file">',
            $filePond,
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('fileValidateTypeDetectType: (source, type) =>', $result);
        $this->assertStringContainsString('functionFileRename()', $result);
    }

    public function testLabelIdle(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'labelIdle' => 'Drag & Drop or <span class="filepond--label-action"> Browse </span>',
                'model' => new TestForm(),
            ],
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString(
            '"labelIdle":"Drag & Drop or <span class=\"filepond--label-action\"> Browse <\/span>"',
            $result,
        );
    }

    public function testMaxFiles(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'maxFiles' => 3,
                'model' => new TestForm(),
            ],
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('"maxFiles":3', $result);
    }

    public function testName(): void
    {
        $filePond = FilePond::widget(
            [
                'name' => 'filepond',
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="w0-filepond" name="filepond[]" type="file">',
            $filePond,
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('input[type="file"][id="w0-filepond"]', $result);
    }

    public function testNotClassFormControl(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'model' => new TestForm(),
                'options' => ['class' => 'form-control'],
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file">',
            $filePond,
        );

        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'cssClass' => 'form-control',
                'model' => new TestForm(),
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file">',
            $filePond,
        );
    }

    public function testNotPlaceholder(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'model' => new TestForm(),
                'options' => ['placeholder' => 'test-placeholder'],
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file">',
            $filePond,
        );
    }

    public function testPluingDefault(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'maxFiles' => 3,
                'model' => new TestForm(),
            ],
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString('FilePondPluginFileEncode', $result);
        $this->assertStringContainsString('FilePondPluginFileValidateSize', $result);
        $this->assertStringContainsString('FilePondPluginFileValidateType', $result);
        $this->assertStringContainsString('FilePondPluginImageExifOrientation', $result);
        $this->assertStringContainsString('FilePondPluginImagePreview', $result);
    }

    public function testRender(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'model' => new TestForm(),
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file">',
            $filePond,
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertStringContainsString(
            <<<JS
            <script>jQuery(function ($) {
            FilePond.registerPlugin(FilePondPluginFileEncode, FilePondPluginFileValidateSize, FilePondPluginFileValidateType, FilePondPluginImageExifOrientation, FilePondPluginImagePreview)
            FilePond.setOptions({"acceptedFileTypes":[],"allowFileRename":false,"allowFileTypeValidation":true,"allowFileValidateSize":true,"allowImageCrop":false,"allowImageExifOrientation":true,"allowImagePreview":true,"allowImageTransform":false,"allowMultiple":false,"className":"","fileValidateTypeLabelExpectedTypes":"Expects {allButLastType} or {lastType}","imageCropAspectRatio":null,"imagePreviewHeight":null,"imagePreviewMarkupShow":true,"imagePreviewMaxFileSize":null,"imagePreviewMaxHeight":256,"imagePreviewMaxInstantPreviewFileSize":null,"imagePreviewMinHeight":44,"imagePreviewTransparencyIndicator":null,"imageTransformAfterCreateBlob":null,"imageTransformBeforeCreateBlob":null,"imageTransformClientTransforms":null,"imageTransformOutputQuality":null,"imageTransformOutputQualityMode":null,"imageTransformOutputStripImageHead":true,"imageTransformVariants":null,"imageTransformVariantsDefaultName":null,"imageTransformVariantsIncludeOriginal":true,"labelFileTypeNotAllowed":"File type not allowed","labelIdle":"Drag & Drop your files or <span class=\"filepond--label-action\"> Browse <\/span>","labelMaxFileSize":"Maximum file size is {filesize}","labelMaxFileSizeExceeded":"File is too large","labelMaxTotalFileSize":"Maximum total file size is {filesize}","labelMaxTotalFileSizeExceeded":"Maximum total size exceeded","maxFiles":1,"maxFileSize":null,"maxTotalFileSize":null,"minFileSize":null,"pdfPreviewHeight":320,"pdfComponentExtraParams":"toolbar=0&view=fit&page=1","required":false})

            const loadFileDefault = ""
            const pond = FilePond.create(document.querySelector('input[type="file"][id="testform-array"]'), )

            if (loadFileDefault !== '') {
                pond.addFiles(loadFileDefault)
            }
            });</script>
            JS,
            $result,
        );
    }

    public function testRequired(): void
    {
        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'model' => new TestForm(),
                'required' => true,
            ],
        );

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => $filePond]);

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file" required>',
            $filePond,
        );
        $this->assertStringContainsString('"required":true', $result);
    }
}

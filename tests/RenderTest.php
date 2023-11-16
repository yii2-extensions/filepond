<?php

declare(strict_types=1);

namespace Yii\FilePond\Tests;

use Yii;
use Yii\FilePond\FilePond;
use Yii\FilePond\Tests\Support\TestForm;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class RenderTest extends TestCase
{
    public function testAllowMultiple(): void
    {
        $this->mockApplication();

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
    }

    public function testCssClass(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        FilePond::widget(
            [
                'attribute' => 'array',
                'cssClass' => 'TestClass',
                'model' => new TestForm(),
            ],
        );

        $result = $view->renderFile(__DIR__ . '/support/main.php');

        $this->assertStringContainsString('"className":"TestClass"', $result);
    }

    public function testConfig(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        FilePond::widget(
            [
                'attribute' => 'array',
                'config' => ['forceRevert' => true, 'storeAsFile' => true],
                'model' => new TestForm(),
            ],
        );

        $result = $view->renderFile(__DIR__ . '/support/main.php');

        $this->assertStringContainsString('"forceRevert":true,"storeAsFile":true', $result);
    }

    public function testConfigWithClassName(): void
    {
        $this->mockApplication();

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
    }

    public function testFileRename(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

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

        $result = $view->renderFile(__DIR__ . '/support/main.php');

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

        $result = $view->renderFile(__DIR__ . '/support/main.php');

        $this->assertStringContainsString('fileValidateTypeDetectType: (source, type) =>', $result);
        $this->assertStringContainsString('functionFileRename()', $result);
    }

    public function testLabelIdle(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        FilePond::widget(
            [
                'attribute' => 'array',
                'labelIdle' => 'Drag & Drop or <span class="filepond--label-action"> Browse </span>',
                'model' => new TestForm(),
            ],
        );

        $result = $view->renderFile(__DIR__ . '/support/main.php');

        $this->assertStringContainsString(
            '"labelIdle":"Drag & Drop or <span class=\"filepond--label-action\"> Browse <\/span>"',
            $result,
        );
    }

    public function testMaxFiles(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        FilePond::widget(
            [
                'attribute' => 'array',
                'maxFiles' => 3,
                'model' => new TestForm(),
            ],
        );

        $result = $view->renderFile(__DIR__ . '/support/main.php');

        $this->assertStringContainsString('"maxFiles":3', $result);
    }

    public function testName(): void
    {
        $this->mockApplication();

        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'attributes' => ['name' => 'test-name'],
                'model' => new TestForm(),
            ],
        );

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="test-name[]" type="file">',
            $filePond,
        );
    }

    public function testPluingDefault(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        FilePond::widget(
            [
                'attribute' => 'array',
                'maxFiles' => 3,
                'model' => new TestForm(),
            ],
        );

        $result = $view->renderFile(__DIR__ . '/support/main.php');

        $this->assertStringContainsString('FilePondPluginFileEncode', $result);
        $this->assertStringContainsString('FilePondPluginFileValidateSize', $result);
        $this->assertStringContainsString('FilePondPluginFileValidateType', $result);
        $this->assertStringContainsString('FilePondPluginImageExifOrientation', $result);
        $this->assertStringContainsString('FilePondPluginImagePreview', $result);
    }

    public function testRender(): void
    {
        $this->mockApplication();

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
    }

    public function testRequired(): void
    {
        $this->mockApplication();

        $view = Yii::$app->getView();

        $filePond = FilePond::widget(
            [
                'attribute' => 'array',
                'model' => new TestForm(),
                'required' => true,
            ],
        );

        $result = $view->renderFile(__DIR__ . '/support/main.php');

        $this->assertSame(
            '<input class="filepond" id="testform-array" name="TestForm[array][]" type="file" required>',
            $filePond,
        );
        $this->assertStringContainsString('"required":true', $result);
    }
}

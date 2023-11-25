<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Tests;

use Yii2\Extensions\FilePond\Asset;
use Yii2\Extensions\FilePond\FilePond;
use Yii;
use yii\web\AssetBundle;
use yii\web\View;

final class AssetTest extends TestCase
{
    public function setup(): void
    {
        parent::setUp();
        $this->mockApplication();

        FilePond::$counter = 0;

        $this->view = Yii::$app->getView();
    }

    public function testFilePondAssetSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondAsset::register($this->view);

        $this->assertCount(6, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateTypePlugin::class, $this->view->assetBundles);
    }

    public function testFilePondAssetRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondAsset::register($this->view);

        $this->assertCount(6, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString('/dist/filepond.css', $result);
        $this->assertStringContainsString('/dist/filepond.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-encode.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-exif-orientation.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-size.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-type.js', $result);
    }

    public function testFilePondCdnAssetSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondCdnAsset::register($this->view);

        $this->assertCount(6, $this->view->assetBundles);

        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateTypePlugin::class, $this->view->assetBundles);
    }

    public function testFilePondCdnAssetRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondCdnAsset::register($this->view);

        $this->assertCount(6, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
            HTML,
            $result,
        );
    }

    public function testFilePondImageCropPluginSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondImageCropPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageCropPlugin::class, $this->view->assetBundles);
    }

    public function testFilePondImageCropPluginRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondImageCropPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.css', $result);
        $this->assertStringContainsString('/dist/filepond.css', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-encode.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-exif-orientation.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-size.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-type.js', $result);
        $this->assertStringContainsString('/dist/filepond.js', $result);
        $this->assertStringContainsString('filepond-plugin-image-crop.js', $result);
    }

    public function testFilePondImageCropPluginCdnSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\FilePondImageCropPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageCropPlugin::class, $this->view->assetBundles);
    }

    public function testFilePondImageCropPluginCdnRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\FilePondImageCropPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-crop/dist/filepond-plugin-image-crop.min.js"></script>
            HTML,
            $result,
        );
    }

    public function testFilePondImageTransformPluginSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondImageTransformPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageTransformPlugin::class, $this->view->assetBundles);
    }

    public function testFilePondImageTransformPluginRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondImageTransformPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.css', $result);
        $this->assertStringContainsString('/dist/filepond.css', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-encode.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-exif-orientation.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-size.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-type.js', $result);
        $this->assertStringContainsString('/dist/filepond.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-transform.js', $result);
    }

    public function testFilePondImageTransformPluginCdnSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\FilePondImageTransformPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageTransformPlugin::class, $this->view->assetBundles);
    }

    public function testFilePondImageTransformPluginCdnRegister(): void
    {
        $this->mockApplication();

        $view = new View();

        $this->assertEmpty($view->assetBundles);

        Asset\Cdn\FilePondImageTransformPlugin::register($view);

        $this->assertCount(7, $view->assetBundles);

        $result = $view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.min.js"></script>
            HTML,
            $result,
        );
    }

    public function testFilePondPdfPreviewPluginSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondPdfPreviewPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondPdfPreviewPlugin::class, $this->view->assetBundles);
    }

    public function testFilePondPdfPreviewPluginRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondPdfPreviewPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.css', $result);
        $this->assertStringContainsString('/dist/filepond.css', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-encode.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-exif-orientation.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-size.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-type.js', $result);
        $this->assertStringContainsString('/dist/filepond.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-pdf-preview.js', $result);
    }

    public function testFilePondPdfPreviewPluginCdnSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\FilePondPdfPreviewPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondPdfPreviewPlugin::class, $this->view->assetBundles);
    }

    public function testFilePondPdfPreviewPluginCdnRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\FilePondPdfPreviewPlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-pdf-preview/dist/filepond-plugin-pdf-preview.min.css" rel="stylesheet">
            HTML,
            $result,
        );
    }

    public function testFilePondRenamePluginSimpleDependency(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondRenamePlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $this->assertArrayHasKey(Asset\FilePondAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondRenamePlugin::class, $this->view->assetBundles);
    }

    public function testFilePondRenamePluginRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\FilePondRenamePlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.css', $result);
        $this->assertStringContainsString('/dist/filepond.css', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-encode.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-exif-orientation.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-image-preview.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-size.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-validate-type.js', $result);
        $this->assertStringContainsString('/dist/filepond.js', $result);
        $this->assertStringContainsString('/dist/filepond-plugin-file-rename.js', $result);
    }

    public function testFilePondRenamePluginCdnSimpleDependency(): void
    {
        Asset\Cdn\FilePondRenamePlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\FilePondCdnAsset::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondEncodePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImageExifOrientationPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondImagePreviewPlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateSizePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondValidateTypePlugin::class, $this->view->assetBundles);
        $this->assertArrayHasKey(Asset\Cdn\FilePondRenamePlugin::class, $this->view->assetBundles);
    }

    public function testFilePondRenamePluginCdnRegister(): void
    {
        $this->assertEmpty($this->view->assetBundles);

        Asset\Cdn\FilePondRenamePlugin::register($this->view);

        $this->assertCount(7, $this->view->assetBundles);

        $result = $this->view->renderFile(__DIR__ . '/Support/main.php', ['widget' => '']);

        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <link href="https://unpkg.com/filepond@^4/dist/filepond.min.css" rel="stylesheet">
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond@^4/dist/filepond.min.js"></script>
            HTML,
            $result,
        );
        $this->assertStringContainsString(
            <<<HTML
            <script src="https://unpkg.com/filepond-plugin-file-rename/dist/filepond-plugin-file-rename.js"></script>
            HTML,
            $result,
        );
    }
}

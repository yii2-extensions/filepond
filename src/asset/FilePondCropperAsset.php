<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset;

use yii2\extensions\filepond\asset\plugin\ImageEditAsset;

/**
 * Delivers the Cropper.js editor adapter for the FilePond Image Edit plugin.
 *
 * The adapter is exposed as `window.yii2FilePond.cropper` and is registered automatically by the widget when
 * `allowImageEdit` is enabled.
 */
final class FilePondCropperAsset extends AbstractLocalAsset
{
    public $depends = [
        CropperAsset::class,
        FilePondWidgetAsset::class,
        ImageEditAsset::class,
    ];
    public $sourcePath = __DIR__ . '/cropper';

    protected function scripts(): array
    {
        return ['filepond-cropper.js'];
    }

    protected function styles(): array
    {
        return ['filepond-cropper.css'];
    }
}

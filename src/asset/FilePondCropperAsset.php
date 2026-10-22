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
    /**
     * Bundles the adapter requires: Cropper.js, the widget runtime, and the Image Edit plugin.
     */
    public $depends = [
        CropperAsset::class,
        FilePondWidgetAsset::class,
        ImageEditAsset::class,
    ];
    /**
     * Published source directory of the packaged editor adapter files.
     */
    public $sourcePath = __DIR__ . '/cropper';

    /**
     * Returns the editor adapter script file name.
     *
     * @return list<string> Script file names.
     */
    protected function scripts(): array
    {
        return ['filepond-cropper.js'];
    }

    /**
     * Returns the editor adapter stylesheet file name.
     *
     * @return list<string> Stylesheet file names.
     */
    protected function styles(): array
    {
        return ['filepond-cropper.css'];
    }
}

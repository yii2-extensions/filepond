<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset;

/**
 * Delivers the Cropper.js library used by the image editor.
 *
 * @see https://fengyuanchen.github.io/cropperjs/
 */
final class CropperAsset extends AbstractNpmAsset
{
    protected function package(): string
    {
        return 'cropperjs';
    }

    protected function scripts(): array
    {
        return ['cropper.js'];
    }
}

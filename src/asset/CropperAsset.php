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
    /**
     * Returns the npm package that ships Cropper.js.
     *
     * @return string npm package name.
     */
    protected function package(): string
    {
        return 'cropperjs';
    }

    /**
     * Returns the Cropper.js script file name.
     *
     * @return list<string> Script file names.
     */
    protected function scripts(): array
    {
        return ['cropper.js'];
    }
}

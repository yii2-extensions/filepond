<?php

declare(strict_types=1);

namespace Yii\FilePond\Asset\Cdn;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond image preview plugin in content delivery network (CDN) mode, mainly used for publishing
 * assets.
 */
final class FilePondImagePreviewPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $css = [
        'https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css',
    ];

    /**
     * {@inheritDoc}
     */
    public $js = [
        'https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js',
    ];
}

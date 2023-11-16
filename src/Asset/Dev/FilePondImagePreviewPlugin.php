<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Dev;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond image preview plugin in development mode, mainly used for publishing assets.
 */
final class FilePondImagePreviewPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-image-preview';

    /**
     * {@inheritDoc}
     */
    public $css = [
        'dist/filepond-plugin-image-preview.css',
    ];

    /**
     * {@inheritDoc}
     */
    public $js = [
        'dist/filepond-plugin-image-preview.js',
    ];

    /**
     * {@inheritDoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond-plugin-image-preview.css',
            'dist/filepond-plugin-image-preview.js',
        ],
    ];
}

<?php

declare(strict_types=1);

namespace Yii\FilePond\Asset\Prod;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond image exif orientation plugin in production mode, mainly used for publishing assets.
 */
final class FilePondImageExifOrientationPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-image-exif-orientation';

    /**
     * {@inheritDoc}
     */
    public $js = [
        'dist/filepond-plugin-image-exif-orientation.min.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond-plugin-image-exif-orientation.min.js',
        ],
    ];
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Dev;

use Yii2\Extensions\FilePond\Asset\FilePondAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond image crop plugin in development mode, mainly used for publishing assets.
 */
final class FilePondImageCropPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-image-crop';

    /**
     * {@inheritDoc}
     */
    public $js = [
        'dist/filepond-plugin-image-crop.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond-plugin-image-crop.js',
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        FilePondAsset::class,
    ];
}

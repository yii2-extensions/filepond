<?php

declare(strict_types=1);

namespace Yii\FilePond\Asset\Cdn;

use Yii\FilePond\Asset\FilePondAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond image crop plugin in content delivery network (CDN) mode, mainly used for publishing
 * assets.
 */
final class FilePondImageCropPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $js = [
        'https://unpkg.com/filepond-plugin-image-crop/dist/filepond-plugin-image-crop.min.js',
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        FilePondAsset::class,
    ];
}

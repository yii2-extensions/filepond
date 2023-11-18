<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Cdn;

use Yii2\Extensions\FilePond\Asset\FilePondCdnAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond image transform plugin in content delivery network (CDN) mode, mainly used for publishing
 * assets.
 */
final class FilePondImageTransformPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $js = [
        'https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.min.js',
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        FilePondCdnAsset::class,
    ];
}

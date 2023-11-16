<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond widget in content delivery network, mainly used for publishing assets.
 */
final class FilePondCdnAsset extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $css = [
        'https://unpkg.com/filepond@^4/dist/filepond.min.css',
    ];

    /**
     * {@inheritDoc}
     */
    public $js = [
        'https://unpkg.com/filepond@^4/dist/filepond.min.js',
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        Cdn\FilePondEncodePlugin::class,
        Cdn\FilePondImageExifOrientationPlugin::class,
        Cdn\FilePondImagePreviewPlugin::class,
        Cdn\FilePondValidateSizePlugin::class,
        Cdn\FilePondValidateTypePlugin::class,
    ];
}

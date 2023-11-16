<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond widget in production mode, mainly used for publishing assets.
 */
final class FilePondProdAsset extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond';

    /**
     * {@inheritDoc}
     */
    public $css = [
        'dist/filepond.min.css',
    ];

    /**
     * {@inheritDoc}
     */
    public $js = [
        'dist/filepond.min.js',
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        Prod\FilePondEncodePlugin::class,
        Prod\FilePondImageExifOrientationPlugin::class,
        Prod\FilePondImagePreviewPlugin::class,
        Prod\FilePondValidateSizePlugin::class,
        Prod\FilePondValidateTypePlugin::class,
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond.min.css',
            'dist/filepond.min.js',
        ],
    ];
}

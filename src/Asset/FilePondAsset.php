<?php

declare(strict_types=1);

namespace Yii\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond widget in development mode, mainly used for publishing assets.
 */
final class FilePondAsset extends AssetBundle
{
    /**
     * {@inheritdoc}
     */
    public $sourcePath = '@npm/filepond';

    /**
     * {@inheritdoc}
     */
    public $css = [
        'dist/filepond.css',
    ];

    /**
     * {@inheritdoc}
     */
    public $js = [
        'dist/filepond.js'
    ];

    /**
     * {@inheritdoc}
     */
    public $depends = [
        Dev\FilePondEncodePlugin::class,
        Dev\FilePondImageExifOrientationPlugin::class,
        Dev\FilePondImagePreviewPlugin::class,
        Dev\FilePondValidateSizePlugin::class,
        Dev\FilePondValidateTypePlugin::class,
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond.css',
            'dist/filepond.js',
        ],
    ];
}

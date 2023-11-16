<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Dev;

use Yii2\Extensions\FilePond\Asset\FilePondAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond image transform plugin in development mode, mainly used for publishing assets.
 */
final class FilePondImageTransformPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-image-transform';

    /**
     * {@inheritDoc}
     */
    public $js = [
        'dist/filepond-plugin-image-transform.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond-plugin-image-transform.js',
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        FilePondAsset::class,
    ];
}

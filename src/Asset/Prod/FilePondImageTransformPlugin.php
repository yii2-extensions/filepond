<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Prod;

use Yii2\Extensions\FilePond\Asset\FilePondAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond image transform plugin in production mode, mainly used for publishing assets.
 */
final class FilePondImageTransformPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-image-transform-plugin';

    /**
     * {@inheritDoc}
     */
    public $js = [
        'dist/filepond-plugin-image-transform.min.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond-plugin-image-transform.min.js',
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        FilePondAsset::class,
    ];
}

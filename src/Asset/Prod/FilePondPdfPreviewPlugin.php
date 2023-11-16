<?php

declare(strict_types=1);

namespace Yii\FilePond\Asset\Prod;

use Yii\FilePond\Asset\FilePondProdAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond pdf preview plugin in production mode, mainly used for publishing assets.
 */
final class FilePondPdfPreviewPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-pdf-preview';

    /**
     * {@inheritDoc}
     */
    public $css = [
        'dist/filepond-plugin-pdf-preview.min.css',
    ];

    /**
     * {@inheritDoc}
     */
    public $js = [
        'dist/filepond-plugin-pdf-preview.min.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond-plugin-pdf-preview.min.css',
            'dist/filepond-plugin-pdf-preview.min.js',
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        FilePondProdAsset::class,
    ];
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Dev;

use Yii2\Extensions\FilePond\Asset\FilePondAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond pdf preview plugin in development mode, mainly used for publishing assets.
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
        'dist/filepond-plugin-pdf-preview.css',
    ];

    /**
     * {@inheritDoc}
     */
    public $js = [
        'dist/filepond-plugin-pdf-preview.js',
    ];

    /**
     * {@inheritDoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond-plugin-pdf-preview.css',
            'dist/filepond-plugin-pdf-preview.js',
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        FilePondAsset::class,
    ];
}

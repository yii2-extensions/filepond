<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Cdn;

use Yii2\Extensions\FilePond\Asset\FilePondAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond pdf preview plugin in content delivery network (CDN) mode, mainly used for publishing
 * assets.
 */
final class FilePondPdfPreviewPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $css = [
        'https://unpkg.com/filepond-plugin-pdf-preview/dist/filepond-plugin-pdf-preview.min.css',
    ];

    /**
     * {@inheritDoc}
     */
    public $js = [
        'https://unpkg.com/filepond-plugin-pdf-preview/dist/filepond-plugin-pdf-preview.min.js',
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        FilePondAsset::class,
    ];
}

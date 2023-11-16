<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Prod;

use Yii2\Extensions\FilePond\Asset\FilePondProdAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond rename plugin in production mode, mainly used for publishing assets.
 */
final class FilePondRenamePlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-file-rename-plugin';

    /**
     * {@inheritDoc}
     */
    public $js = [
        'dist/filepond-plugin-file-rename.min.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond-plugin-file-rename.min.js',
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        FilePondProdAsset::class,
    ];
}

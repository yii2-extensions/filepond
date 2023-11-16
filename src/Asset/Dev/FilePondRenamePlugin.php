<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Dev;

use Yii2\Extensions\FilePond\Asset\FilePondAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond rename plugin in development mode, mainly used for publishing assets.
 */
final class FilePondRenamePlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-file-rename';

    /**
     * {@inheritDoc}
     */
    public $js = [
        'dist/filepond-plugin-file-rename.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond-plugin-file-rename.js',
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        FilePondAsset::class,
    ];
}

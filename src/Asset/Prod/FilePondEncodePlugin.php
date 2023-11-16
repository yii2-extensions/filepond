<?php

declare(strict_types=1);

namespace Yii\FilePond\Asset\Prod;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond encode plugin in production mode, mainly used for publishing assets.
 */
final class FilePondEncodePlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-file-encode';

    /**
     * {@inheritDoc}
     */
    public $js = [
        'dist/filepond-plugin-file-encode.min.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond-plugin-file-encode.min.js',
        ],
    ];
}

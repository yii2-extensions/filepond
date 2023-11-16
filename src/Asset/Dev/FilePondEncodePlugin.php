<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Dev;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond encode plugin in development mode, mainly used for publishing assets.
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
        'dist/filepond-plugin-file-encode.js',
    ];

    /**
     * {@inheritDoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond-plugin-file-encode.js',
        ],
    ];
}

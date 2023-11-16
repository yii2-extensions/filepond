<?php

declare(strict_types=1);

namespace Yii\FilePond\Asset\Prod;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond validate size plugin in production mode, mainly used for publishing assets.
 */
final class FilePondValidateSizePlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-file-validate-size';

    /**
     * {@inheritDoc}
     */
    public $js = [
        'dist/filepond-plugin-file-validate-size.min.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond-plugin-file-validate-size.min.js',
        ],
    ];
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Dev;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond validate type plugin in development mode, mainly used for publishing assets.
 */
final class FilePondValidateTypePlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-file-validate-type';

    /**
     * {@inheritDoc}
     */
    public $js = [
        'dist/filepond-plugin-file-validate-type.js',
    ];

    /**
     * {@inheritDoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond-plugin-file-validate-type.js',
        ],
    ];
}

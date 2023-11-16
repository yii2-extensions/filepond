<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Prod;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond validate type plugin in production mode, mainly used for publishing assets.
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
        'dist/filepond-plugin-file-validate-type.min.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'only' => [
            'dist/filepond-plugin-file-validate-type.min.js',
        ],
    ];
}

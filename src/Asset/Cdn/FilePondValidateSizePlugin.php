<?php

declare(strict_types=1);

namespace Yii\FilePond\Asset\Cdn;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond validate size plugin in content delivery network (CDN) mode, mainly used for publishing
 * assets.
 */
final class FilePondValidateSizePlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $js = [
        'https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js',
    ];
}

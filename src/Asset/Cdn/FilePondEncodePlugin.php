<?php

declare(strict_types=1);

namespace Yii\FilePond\Asset\Cdn;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond encode plugin in content delivery network (CDN) mode, mainly used for publishing
 * assets.
 */
final class FilePondEncodePlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $js = [
        'https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js',
    ];
}

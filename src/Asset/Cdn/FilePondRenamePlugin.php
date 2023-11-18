<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Cdn;

use Yii2\Extensions\FilePond\Asset\FilePondCdnAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond rename plugin in content delivery network (CDN) mode, mainly used for publishing
 * assets.
 */
final class FilePondRenamePlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $js = [
        'https://unpkg.com/filepond-plugin-file-rename/dist/filepond-plugin-file-rename.js',
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        FilePondCdnAsset::class,
    ];
}

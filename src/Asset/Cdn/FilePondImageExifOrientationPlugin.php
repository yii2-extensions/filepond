<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset\Cdn;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond image exif orientation plugin in content delivery network (CDN) mode, mainly used for
 * publishing assets.
 */
final class FilePondImageExifOrientationPlugin extends AssetBundle
{
    /**
     * {@inheritdoc}
     */
    public $js = [
        'https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js',
    ];
}

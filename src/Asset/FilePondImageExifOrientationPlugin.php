<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond image exif orientation plugin, mainly used for publishing assets.
 */
final class FilePondImageExifOrientationPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-image-exif-orientation';

    public function init(): void
    {
        parent::init();

        $this->js = YII_ENV === 'prod'
            ? ['dist/filepond-plugin-image-exif-orientation.min.js'] : ['dist/filepond-plugin-image-exif-orientation.js'];

        $this->publishOptions['only'] = $this->js;
    }
}

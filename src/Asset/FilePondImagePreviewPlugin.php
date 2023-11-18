<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond image preview plugin, mainly used for publishing assets.
 */
final class FilePondImagePreviewPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-image-preview';

    public function init(): void
    {
        parent::init();

        $this->css = YII_ENV === 'prod'
            ? ['dist/filepond-plugin-image-preview.min.css'] : ['dist/filepond-plugin-image-preview.css'];

        $this->js = YII_ENV === 'prod'
            ? ['dist/filepond-plugin-image-preview.min.js'] : ['dist/filepond-plugin-image-preview.js'];

        $this->publishOptions['only'] = array_merge($this->css, $this->js);
    }
}

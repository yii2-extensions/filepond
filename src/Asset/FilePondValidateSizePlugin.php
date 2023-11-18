<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond validate size plugin, mainly used for publishing assets.
 */
final class FilePondValidateSizePlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-file-validate-size';

    public function init(): void
    {
        parent::init();

        $this->js = YII_ENV === 'prod'
            ? ['dist/filepond-plugin-file-validate-size.min.js'] : ['dist/filepond-plugin-file-validate-size.js'];

        $this->publishOptions['only'] = $this->js;
    }
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond validate type plugin, mainly used for publishing assets.
 */
final class FilePondValidateTypePlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-file-validate-type';

    public function init(): void
    {
        parent::init();

        $this->js = YII_ENV === 'prod'
            ? ['dist/filepond-plugin-file-validate-type.min.js'] : ['dist/filepond-plugin-file-validate-type.js'];

        $this->publishOptions['only'] = $this->js;
    }
}

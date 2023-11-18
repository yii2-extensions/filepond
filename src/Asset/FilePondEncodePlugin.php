<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond encode plugin, mainly used for publishing assets.
 */
final class FilePondEncodePlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-file-encode';

    public function init(): void
    {
        parent::init();

        $this->js = YII_ENV === 'prod'
            ? ['dist/filepond-plugin-file-encode.min.js'] : ['dist/filepond-plugin-file-encode.js'];

        $this->publishOptions['only'] = $this->js;
    }
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use Yii2\Extensions\FilePond\Asset\FilePondAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond image crop plugin, mainly used for publishing assets.
 */
final class FilePondImageCropPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-image-crop';

    public function init(): void
    {
        parent::init();

        $this->js = YII_ENV === 'prod'
            ? ['dist/filepond-plugin-image-crop.min.js'] : ['dist/filepond-plugin-image-crop.js'];

        $this->publishOptions['only'] = $this->js;
    }

    /**
     * {@inheritDoc}
     *
     * @phpstan-var array<array-key, mixed>
     */
    public $depends = [
        FilePondAsset::class,
    ];
}

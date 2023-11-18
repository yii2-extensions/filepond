<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond widget in development mode, mainly used for publishing assets.
 */
final class FilePondAsset extends AssetBundle
{
    /**
     * {@inheritdoc}
     */
    public $sourcePath = '@npm/filepond';

    public function init(): void
    {
        parent::init();

        $this->css = YII_ENV === 'prod'
            ? ['dist/filepond.min.css'] : ['dist/filepond.css'];

        $this->js = YII_ENV === 'prod'
            ? ['dist/filepond.min.js'] : ['dist/filepond.js'];

        $this->publishOptions['only'] = array_merge($this->css, $this->js);
    }

    /**
     * {@inheritdoc}
     *
     * @phpstan-var array<array-key, mixed>
     */
    public $depends = [
        FilePondEncodePlugin::class,
        FilePondImageExifOrientationPlugin::class,
        FilePondImagePreviewPlugin::class,
        FilePondValidateSizePlugin::class,
        FilePondValidateTypePlugin::class,
    ];
}

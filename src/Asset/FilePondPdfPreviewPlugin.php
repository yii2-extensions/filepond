<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use Yii2\Extensions\FilePond\Asset\FilePondAsset;
use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond pdf preview plugin, mainly used for publishing assets.
 */
final class FilePondPdfPreviewPlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-pdf-preview';

    public function init(): void
    {
        parent::init();

        $this->css = YII_ENV === 'prod'
            ? ['dist/filepond-plugin-pdf-preview.min.css'] : ['dist/filepond-plugin-pdf-preview.css'];

        $this->js = YII_ENV === 'prod'
            ? ['dist/filepond-plugin-pdf-preview.min.js'] : ['dist/filepond-plugin-pdf-preview.js'];

        $this->publishOptions['only'] = array_merge($this->css, $this->js);
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

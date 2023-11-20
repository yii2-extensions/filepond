<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Asset;

use yii\web\AssetBundle;

/**
 * Asset bundle for the filepond rename plugin, mainly used for publishing assets.
 */
final class FilePondRenamePlugin extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@npm/filepond-plugin-file-rename';

    public function init(): void
    {
        parent::init();

        $this->js = YII_ENV === 'prod'
            ? ['dist/filepond-plugin-file-rename.min.js'] : ['dist/filepond-plugin-file-rename.js'];

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

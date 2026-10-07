<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset;

use yii\web\AssetBundle;

/**
 * Delivers the widget runtime that registers plugins once and creates FilePond instances per input.
 *
 * The runtime is exposed as `window.yii2FilePond` and is registered automatically by the widget.
 */
final class FilePondWidgetAsset extends AssetBundle
{
    public $css = ['filepond-widget.css'];
    public $depends = [FilePondAsset::class];
    public $js = ['filepond-widget.js'];
    public $sourcePath = __DIR__ . '/widget';
}

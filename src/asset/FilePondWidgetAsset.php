<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset;

/**
 * Delivers the widget runtime that registers plugins once and creates FilePond instances per input.
 *
 * The runtime is exposed as `window.yii2FilePond` and is registered automatically by the widget.
 */
final class FilePondWidgetAsset extends AbstractLocalAsset
{
    public $depends = [FilePondAsset::class];
    public $sourcePath = __DIR__ . '/widget';

    protected function scripts(): array
    {
        return ['filepond-widget.js'];
    }

    protected function styles(): array
    {
        return ['filepond-widget.css'];
    }
}

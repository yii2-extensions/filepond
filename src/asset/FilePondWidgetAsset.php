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
    /**
     * Bundle of the FilePond core library that the runtime drives.
     */
    public $depends = [FilePondAsset::class];
    /**
     * Published source directory of the packaged widget runtime files.
     */
    public $sourcePath = __DIR__ . '/widget';

    /**
     * Returns the widget runtime script file name.
     *
     * @return list<string> Script file names.
     */
    protected function scripts(): array
    {
        return ['filepond-widget.js'];
    }

    /**
     * Returns the widget runtime stylesheet file name.
     *
     * @return list<string> Stylesheet file names.
     */
    protected function styles(): array
    {
        return ['filepond-widget.css'];
    }
}

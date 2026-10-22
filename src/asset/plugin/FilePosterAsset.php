<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset\plugin;

use yii2\extensions\filepond\asset\{AbstractPluginAsset, Plugin};

/**
 * Delivers the FilePond File Poster plugin.
 *
 * @see https://pqina.nl/filepond/docs/api/plugins/file-poster/
 */
final class FilePosterAsset extends AbstractPluginAsset
{
    /**
     * Returns the File Poster plugin delivered by this bundle.
     *
     * @return Plugin Delivered plugin.
     */
    public function plugin(): Plugin
    {
        return Plugin::FILE_POSTER;
    }
}

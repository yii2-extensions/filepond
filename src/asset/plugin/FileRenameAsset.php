<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset\plugin;

use yii2\extensions\filepond\asset\{AbstractPluginAsset, Plugin};

/**
 * Delivers the FilePond File Rename plugin.
 *
 * @see https://pqina.nl/filepond/docs/api/plugins/file-rename/
 */
final class FileRenameAsset extends AbstractPluginAsset
{
    /**
     * Returns the File Rename plugin delivered by this bundle.
     *
     * @return Plugin Delivered plugin.
     */
    public function plugin(): Plugin
    {
        return Plugin::FILE_RENAME;
    }
}

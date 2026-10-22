<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset\plugin;

use yii2\extensions\filepond\asset\{AbstractPluginAsset, Plugin};

/**
 * Delivers the FilePond File Validate Size plugin.
 *
 * @see https://pqina.nl/filepond/docs/api/plugins/file-validate-size/
 */
final class FileValidateSizeAsset extends AbstractPluginAsset
{
    /**
     * Returns the File Validate Size plugin delivered by this bundle.
     *
     * @return Plugin Delivered plugin.
     */
    public function plugin(): Plugin
    {
        return Plugin::FILE_VALIDATE_SIZE;
    }
}

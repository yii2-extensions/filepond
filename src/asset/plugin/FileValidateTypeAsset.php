<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset\plugin;

use yii2\extensions\filepond\asset\{AbstractPluginAsset, Plugin};

/**
 * Delivers the FilePond File Validate Type plugin.
 *
 * @see https://pqina.nl/filepond/docs/api/plugins/file-validate-type/
 */
final class FileValidateTypeAsset extends AbstractPluginAsset
{
    public function plugin(): Plugin
    {
        return Plugin::FILE_VALIDATE_TYPE;
    }
}

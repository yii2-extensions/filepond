<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset\plugin;

use yii2\extensions\filepond\asset\{AbstractPluginAsset, Plugin};

/**
 * Delivers the FilePond File Encode plugin.
 *
 * @see https://pqina.nl/filepond/docs/api/plugins/file-encode/
 */
final class FileEncodeAsset extends AbstractPluginAsset
{
    public function plugin(): Plugin
    {
        return Plugin::FILE_ENCODE;
    }
}

<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset\plugin;

use yii2\extensions\filepond\asset\{AbstractPluginAsset, Plugin};

/**
 * Delivers the FilePond Image Edit plugin.
 *
 * @see https://pqina.nl/filepond/docs/api/plugins/image-edit/
 */
final class ImageEditAsset extends AbstractPluginAsset
{
    public function plugin(): Plugin
    {
        return Plugin::IMAGE_EDIT;
    }
}

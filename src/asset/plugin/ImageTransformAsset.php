<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset\plugin;

use yii2\extensions\filepond\asset\{AbstractPluginAsset, Plugin};

/**
 * Delivers the FilePond Image Transform plugin.
 *
 * @see https://pqina.nl/filepond/docs/api/plugins/image-transform/
 */
final class ImageTransformAsset extends AbstractPluginAsset
{
    public function plugin(): Plugin
    {
        return Plugin::IMAGE_TRANSFORM;
    }
}

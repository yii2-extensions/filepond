<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset\plugin;

use yii2\extensions\filepond\asset\{AbstractPluginAsset, Plugin};

/**
 * Delivers the FilePond Image Crop plugin.
 *
 * @see https://pqina.nl/filepond/docs/api/plugins/image-crop/
 */
final class ImageCropAsset extends AbstractPluginAsset
{
    /**
     * Returns the Image Crop plugin delivered by this bundle.
     *
     * @return Plugin Delivered plugin.
     */
    public function plugin(): Plugin
    {
        return Plugin::IMAGE_CROP;
    }
}

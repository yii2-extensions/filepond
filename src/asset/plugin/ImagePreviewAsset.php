<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset\plugin;

use yii2\extensions\filepond\asset\{AbstractPluginAsset, Plugin};

/**
 * Delivers the FilePond Image Preview plugin.
 *
 * @see https://pqina.nl/filepond/docs/api/plugins/image-preview/
 */
final class ImagePreviewAsset extends AbstractPluginAsset
{
    /**
     * Returns the Image Preview plugin delivered by this bundle.
     *
     * @return Plugin Delivered plugin.
     */
    public function plugin(): Plugin
    {
        return Plugin::IMAGE_PREVIEW;
    }
}

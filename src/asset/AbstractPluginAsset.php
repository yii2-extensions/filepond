<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset;

/**
 * Provides local or CDN delivery for a FilePond plugin distribution.
 *
 * Concrete bundles only declare the {@see Plugin} case they deliver; file names and dependencies derive from it.
 */
abstract class AbstractPluginAsset extends AbstractNpmAsset
{
    /**
     * Returns the plugin delivered by this bundle.
     *
     * @return Plugin Delivered plugin.
     */
    abstract public function plugin(): Plugin;

    public static function dependencies(): array
    {
        return [FilePondAsset::class];
    }

    protected function package(): string
    {
        return $this->plugin()->package();
    }

    protected function scripts(): array
    {
        return [$this->plugin()->package() . '.js'];
    }

    protected function styles(): array
    {
        return $this->plugin()->hasStyles() ? [$this->plugin()->package() . '.css'] : [];
    }
}

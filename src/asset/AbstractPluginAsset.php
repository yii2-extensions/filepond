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

    /**
     * Returns the FilePond core bundle, which every plugin requires.
     *
     * @return list<class-string<\yii\web\AssetBundle>> Dependency bundle classes.
     */
    public static function dependencies(): array
    {
        return [FilePondAsset::class];
    }

    /**
     * Returns the npm package name of the delivered plugin.
     *
     * @return string npm package name.
     */
    protected function package(): string
    {
        return $this->plugin()->package();
    }

    /**
     * Returns the plugin script file name, derived from its npm package name.
     *
     * @return list<string> Script file names.
     */
    protected function scripts(): array
    {
        return [$this->plugin()->package() . '.js'];
    }

    /**
     * Returns the plugin stylesheet file name, or an empty list when the plugin ships no stylesheet.
     *
     * @return list<string> Stylesheet file names, or an empty list when the plugin ships none.
     */
    protected function styles(): array
    {
        return $this->plugin()->hasStyles() ? [$this->plugin()->package() . '.css'] : [];
    }
}

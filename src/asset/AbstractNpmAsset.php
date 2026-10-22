<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\{AssetBundle, AssetManager, View};
use yii2\extensions\filepond\exception\Message;

use function array_map;
use function array_unique;
use function array_values;
use function file_get_contents;
use function is_array;
use function is_string;
use function is_subclass_of;
use function json_decode;
use function rtrim;
use function str_replace;

/**
 * Provides local or CDN delivery for asset bundles backed by an npm package installed under the `@npm` alias.
 *
 * In local mode the bundle publishes the package `dist` files from the `@npm` alias. In CDN mode it links the same
 * files from {@see $cdnUrl}, pinned to the version read from the installed `package.json`, so both delivery modes
 * always serve the same release. The delivery mode is set through the asset manager `bundles` configuration or
 * through {@see registerWith()}.
 */
abstract class AbstractNpmAsset extends AssetBundle
{
    /**
     * Whether to link the files from the CDN instead of publishing the local package.
     */
    public bool $cdn = false;
    /**
     * Base URL of the CDN serving npm packages as `{cdnUrl}/{package}@{version}/dist/{file}`.
     */
    public string $cdnUrl = 'https://unpkg.com';
    /**
     * Whether to serve the minified `dist` files, or `null` to serve them outside debug mode.
     */
    public bool|null $minified = null;

    /**
     * Returns the npm package name that ships the files.
     *
     * @return string npm package name.
     */
    abstract protected function package(): string;

    /**
     * Returns the script file names inside the package `dist` directory, without the `.min` infix.
     *
     * @return list<string> Script file names.
     */
    abstract protected function scripts(): array;

    /**
     * Returns the asset bundle classes this bundle depends on.
     *
     * @return list<class-string<AssetBundle>> Dependency bundle classes.
     */
    public static function dependencies(): array
    {
        return [];
    }

    /**
     * Initializes the bundle file lists for the configured delivery mode.
     *
     * @throws InvalidConfigException if the installed package version cannot be resolved in CDN mode.
     */
    public function init(): void
    {
        $this->depends = array_values(array_unique([...static::dependencies(), ...$this->depends]));

        $this->minified ??= !YII_DEBUG;

        $styles = $this->styles();
        $scripts = $this->scripts();
        $css = $this->distFiles($styles, $this->minified);
        $js = $this->distFiles($scripts, $this->minified);

        if ($this->cdn) {
            $baseUrl = rtrim($this->cdnUrl, '/') . '/' . $this->package() . '@' . $this->packageVersion() . '/';

            $this->css = array_map(static fn(string $file): string => $baseUrl . $file, $css);
            $this->js = array_map(static fn(string $file): string => $baseUrl . $file, $js);
        } else {
            $this->sourcePath = '@npm/' . $this->package();
            $this->css = $css;
            $this->js = $js;
            $this->publishOptions['only'] ??= [
                ...$this->distFiles($styles, false),
                ...$this->distFiles($scripts, false),
                ...$this->distFiles($styles, true),
                ...$this->distFiles($scripts, true),
            ];
        }

        parent::init();
    }

    /**
     * Registers the bundle, and its npm-backed dependencies, with the given delivery mode.
     *
     * Bundles already configured in the asset manager, or already registered, keep their current mode.
     *
     * @param View $view View that renders the asset files.
     * @param bool $cdn Whether to link the files from the CDN.
     *
     * @return static Registered bundle instance.
     */
    public static function registerWith(View $view, bool $cdn = false): static
    {
        self::seed($view->getAssetManager(), static::class, $cdn);

        return static::register($view);
    }

    /**
     * Returns the stylesheet file names inside the package `dist` directory, without the `.min` infix.
     *
     * @return list<string> Stylesheet file names.
     */
    protected function styles(): array
    {
        return [];
    }

    /**
     * Resolves `dist` paths, selecting minified files when {@see $minified} is enabled.
     *
     * @param list<string> $files File names without the `.min` infix.
     * @param bool $minified Whether to select the minified variant.
     *
     * @return list<string> Paths relative to the package root.
     */
    private function distFiles(array $files, bool $minified): array
    {
        return array_map(
            static fn(string $file): string => 'dist/' . ($minified ? str_replace('.', '.min.', $file) : $file),
            $files,
        );
    }

    /**
     * Reads the installed package version from its `package.json`.
     *
     * @throws InvalidConfigException if the package manifest is missing or has no version.
     *
     * @return string Installed package version.
     */
    private function packageVersion(): string
    {
        $path = Yii::getAlias('@npm/' . $this->package() . '/package.json');

        $manifest = @file_get_contents($path);
        $decoded = is_string($manifest) ? json_decode($manifest, true) : null;
        $version = is_array($decoded) ? ($decoded['version'] ?? null) : null;

        if (!is_string($version) || $version === '') {
            throw new InvalidConfigException(
                Message::PACKAGE_VERSION_UNAVAILABLE->getMessage($this->package(), $path),
            );
        }

        return $version;
    }

    /**
     * Stores the delivery mode of a bundle and its npm-backed dependencies before they are instantiated.
     *
     * @param AssetManager $assetManager Asset manager whose `bundles` configuration receives the delivery mode.
     * @param class-string<self> $class Bundle class to seed.
     * @param bool $cdn Whether to link the files from the CDN.
     */
    private static function seed(AssetManager $assetManager, string $class, bool $cdn): void
    {
        $bundles = $assetManager->bundles;

        if ($bundles === false || isset($bundles[$class])) {
            return;
        }

        $bundles[$class] = ['cdn' => $cdn];
        $assetManager->bundles = $bundles;

        foreach ($class::dependencies() as $dependency) {
            if (is_subclass_of($dependency, self::class)) {
                self::seed($assetManager, $dependency, $cdn);
            }
        }
    }
}

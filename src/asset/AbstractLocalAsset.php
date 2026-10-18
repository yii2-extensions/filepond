<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset;

use yii\web\AssetBundle;

use function array_map;
use function array_unique;
use function array_values;
use function strrpos;
use function substr_replace;

/**
 * Provides readable or minified delivery for the scripts and stylesheets shipped inside this package.
 *
 * Subclasses list the readable file names; the minified copies are generated next to them by `npm run build` with a
 * `.min` infix and are served when {@see $minified} is enabled. Both variants are published so changing the delivery
 * mode can reuse an existing asset directory.
 */
abstract class AbstractLocalAsset extends AssetBundle
{
    /**
     * Whether to serve the minified files, or `null` to serve them outside debug mode.
     */
    public bool|null $minified = null;
    /**
     * Returns the script file names inside {@see $sourcePath}, without the `.min` infix.
     *
     * @return list<string> Script file names.
     */
    abstract protected function scripts(): array;
    /**
     * Returns the stylesheet file names inside {@see $sourcePath}, without the `.min` infix.
     *
     * @return list<string> Stylesheet file names.
     */
    abstract protected function styles(): array;

    /**
     * Initializes the bundle file lists for the configured build.
     */
    public function init(): void
    {
        $this->minified ??= !YII_DEBUG;

        $styles = $this->styles();
        $scripts = $this->scripts();

        $this->css = $this->files($styles, $this->minified);
        $this->js = $this->files($scripts, $this->minified);

        $this->publishOptions['only'] ??= array_values(
            array_unique(
                [
                    ...$this->files($styles, false),
                    ...$this->files($scripts, false),
                    ...$this->files($styles, true),
                    ...$this->files($scripts, true),
                ],
            ),
        );

        parent::init();
    }

    /**
     * Resolves file names, selecting minified files when {@see $minified} is enabled.
     *
     * @param list<string> $files File names without the `.min` infix.
     * @param bool $minified Whether to select the minified variant.
     *
     * @return list<string> File names relative to {@see $sourcePath}.
     */
    private function files(array $files, bool $minified): array
    {
        return array_map(
            static fn(string $file): string => $minified ? self::minifiedFile($file) : $file,
            $files,
        );
    }

    /**
     * Returns the file name with the `.min` infix before its last extension, or unchanged when it has no extension.
     *
     * For example, `filepond.cropper.js` becomes `filepond.cropper.min.js`.
     */
    private static function minifiedFile(string $file): string
    {
        $extension = strrpos($file, '.');

        return $extension === false ? $file : substr_replace($file, '.min', $extension, 0);
    }
}

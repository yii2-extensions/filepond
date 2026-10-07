<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset;

use yii\web\AssetBundle;

use function array_map;
use function str_replace;

/**
 * Provides readable or minified delivery for the scripts and stylesheets shipped inside this package.
 *
 * Subclasses list the readable file names; the minified copies are generated next to them by `npm run build` with a
 * `.min` infix and are served when {@see $minified} is enabled. Only the selected files are published.
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

        $this->css = $this->files($this->styles());
        $this->js = $this->files($this->scripts());

        $this->publishOptions['only'] ??= [...$this->css, ...$this->js];

        parent::init();
    }

    /**
     * Resolves file names, selecting minified files when {@see $minified} is enabled.
     *
     * @param list<string> $files File names without the `.min` infix.
     *
     * @return list<string> File names relative to {@see $sourcePath}.
     */
    private function files(array $files): array
    {
        $minified = $this->minified === true;

        return array_map(
            static fn(string $file): string => $minified ? str_replace('.', '.min.', $file) : $file,
            $files,
        );
    }
}

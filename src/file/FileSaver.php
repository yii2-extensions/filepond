<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\file;

use RuntimeException;
use Yii;
use yii\helpers\FileHelper;
use yii2\extensions\filepond\exception\Message;

use function array_values;
use function chmod;
use function in_array;
use function is_dir;
use function is_string;
use function mkdir;
use function pathinfo;
use function preg_replace;
use function trim;

/**
 * Writes encoded FilePond uploads to a directory with sanitized file names.
 */
final readonly class FileSaver
{
    /**
     * Preferred extensions for MIME types whose first Yii mapping is uncommon.
     */
    private const array MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'text/plain' => 'txt',
    ];

    /**
     * @param string $directory Target directory, optionally as a registered Yii alias; created when missing.
     * @param int $directoryMode Permissions applied to directories created by the saver.
     * @param int $fileMode Permissions applied to written files.
     */
    public function __construct(
        private string $directory,
        private int $directoryMode = 0o775,
        private int $fileMode = 0o644,
    ) {}

    /**
     * Writes the file and returns its absolute path, replacing a file with the same name.
     *
     * The extension follows the MIME type detected from the content: the client extension is kept only when it is
     * registered for that type, so a polyglot such as `avatar.php` holding GIF data is stored as `.gif`. The base name
     * is sanitized to `[A-Za-z0-9_-]`, so passing `$name` is the reliable way to control the stored file name.
     *
     * @param EncodedFile $file File to write.
     * @param string|null $name Base name without extension, or `null` to derive it from the client file name.
     *
     * @throws RuntimeException if the directory is not writable or the file cannot be written.
     *
     * @return string Absolute path of the written file.
     */
    public function save(EncodedFile $file, string|null $name = null): string
    {
        $path = self::path($this->resolveDirectory(), self::baseName($file, $name), self::extension($file));

        return $this->write($file, $path);
    }

    /**
     * Writes every file and returns their absolute paths.
     *
     * When `$name` is given, the first file keeps it and the following ones receive a numeric suffix. Files that
     * would share a path within the batch, such as two uploads with the same client name, receive a `-1`, `-2`, ...
     * suffix instead of overwriting each other.
     *
     * @param iterable<EncodedFile> $files Files to write.
     * @param string|null $name Base name without extension, or `null` to derive it from each client file name.
     *
     * @throws RuntimeException if the directory is not writable or a file cannot be written.
     *
     * @return list<string> Absolute paths of the written files.
     */
    public function saveAll(iterable $files, string|null $name = null): array
    {
        $directory = $this->resolveDirectory();

        $paths = [];

        foreach (array_values([...$files]) as $index => $file) {
            $baseName = self::baseName($file, $name === null || $index === 0 ? $name : "{$name}-{$index}");
            $extension = self::extension($file);
            $path = self::path($directory, $baseName, $extension);

            for ($suffix = 1; in_array($path, $paths, true); $suffix++) {
                $path = self::path($directory, "{$baseName}-{$suffix}", $extension);
            }

            $paths[] = $this->write($file, $path);
        }

        return $paths;
    }

    private static function baseName(EncodedFile $file, string|null $name): string
    {
        $baseName = self::sanitize($name ?? pathinfo($file->name, PATHINFO_FILENAME));

        return $baseName === '' ? 'file' : $baseName;
    }

    /**
     * Returns the client extension when it is registered for the detected MIME type, otherwise the preferred extension
     * of that type, or an empty string for unmapped types.
     */
    private static function extension(EncodedFile $file): string
    {
        $mimeType = $file->getMimeType();

        $extensions = FileHelper::getExtensionsByMimeType($mimeType);

        if (in_array($file->getExtension(), $extensions, true)) {
            return $file->getExtension();
        }

        $extension = self::MIME_EXTENSIONS[$mimeType] ?? $extensions[0] ?? null;

        return is_string($extension) ? $extension : '';
    }

    private static function path(string $directory, string $baseName, string $extension): string
    {
        return $directory . DIRECTORY_SEPARATOR . $baseName . ($extension === '' ? '' : ".{$extension}");
    }

    /**
     * @throws RuntimeException if the directory cannot be created or is not writable.
     */
    private function resolveDirectory(): string
    {
        $directory = Yii::getAlias($this->directory);

        if (!is_dir($directory)) {
            @mkdir($directory, $this->directoryMode, true);
        }

        if (!is_dir($directory) || !is_writable($directory)) {
            throw new RuntimeException(
                Message::DIRECTORY_NOT_WRITABLE->getMessage($directory),
            );
        }

        return $directory;
    }

    /**
     * Keeps `[A-Za-z0-9_-]`, collapsing other characters, dots included, to a dash and trimming dashes, so the base
     * name cannot carry a second extension.
     */
    private static function sanitize(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9_-]+/', '-', $value) ?? '';
        $value = preg_replace('/-{2,}/', '-', $value) ?? '';

        return trim($value, '-');
    }

    /**
     * @throws RuntimeException if the file cannot be written.
     */
    private function write(EncodedFile $file, string $path): string
    {
        if (@file_put_contents($path, $file->getData(), LOCK_EX) === false) {
            throw new RuntimeException(
                Message::FILE_WRITE_FAILED->getMessage($path),
            );
        }

        @chmod($path, $this->fileMode);

        return $path;
    }
}

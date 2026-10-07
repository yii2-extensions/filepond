<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\file;

use RuntimeException;
use Yii;
use yii\helpers\FileHelper;
use yii2\extensions\filepond\exception\Message;

use function array_values;
use function chmod;
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
     * Writes the file and returns its absolute path.
     *
     * The extension comes from the client file name, falling back to the detected MIME type. The base name is
     * sanitized to `[A-Za-z0-9._-]`, so passing `$name` is the reliable way to control the stored file name.
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
        $directory = $this->resolveDirectory();

        $baseName = self::sanitize($name ?? pathinfo($file->name, PATHINFO_FILENAME));

        $baseName = $baseName === '' ? 'file' : $baseName;

        $extension = self::sanitize($file->getExtension());

        if ($extension === '') {
            $extension = self::extensionFromMimeType($file->getMimeType());
        }

        $path = $directory . DIRECTORY_SEPARATOR . $baseName . ($extension === '' ? '' : ".{$extension}");

        if (@file_put_contents($path, $file->getData(), LOCK_EX) === false) {
            throw new RuntimeException(
                Message::FILE_WRITE_FAILED->getMessage($path),
            );
        }

        @chmod($path, $this->fileMode);

        return $path;
    }

    /**
     * Writes every file and returns their absolute paths.
     *
     * When `$name` is given, the first file keeps it and the following ones receive a numeric suffix.
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
        $paths = [];

        foreach (array_values([...$files]) as $index => $file) {
            $paths[] = $this->save($file, $name === null || $index === 0 ? $name : "{$name}-{$index}");
        }

        return $paths;
    }

    private static function extensionFromMimeType(string $mimeType): string
    {
        $known = [
            'image/jpeg' => 'jpg',
            'text/plain' => 'txt',
        ];

        if (isset($known[$mimeType])) {
            return $known[$mimeType];
        }

        $extension = FileHelper::getExtensionsByMimeType($mimeType)[0] ?? null;

        return is_string($extension) ? $extension : '';
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
     * Keeps `[A-Za-z0-9._-]`, collapsing other characters to a dash and trimming leading dots and dashes.
     */
    private static function sanitize(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9._-]+/', '-', $value) ?? '';
        $value = preg_replace('/-{2,}/', '-', $value) ?? '';

        return trim($value, '-.');
    }
}

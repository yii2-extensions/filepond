<?php

declare(strict_types=1);

namespace Yii\FilePond;

use function base64_decode;
use function fclose;
use function fopen;
use function fwrite;
use function is_string;
use function json_decode;
use function pathinfo;
use function preg_replace;

final class FileProcessing
{
    public static function save(array $files, string $path): void
    {
        self::processFiles($files, $path);
    }

    public static function saveWithReturningfile(
        array $files,
        string $path,
        string $newFileName = null,
        bool $withPath = true,
    ): string {
        $savedFiles = self::processFiles($files, $path, $newFileName, $withPath);

        return $savedFiles[0] ?? '';
    }

    public static function saveWithReturningFiles(array $files, string $path, bool $withPath = true): array
    {
        return self::processFiles($files, $path, withPath: $withPath);
    }

    private static function processFiles(
        array $files,
        string $path,
        string $newFileName = null,
        bool $withPath = true
    ): array {
        $savedFiles = [];

        foreach ($files as $file) {
            if (is_string($file) && $file !== '') {
                $file = json_decode($file, false, flags: JSON_THROW_ON_ERROR);
            }

            if (is_object($file) && is_string($file->data) && is_string($file->name)) {
                $filename = self::sanitizeFilename($file->name, $newFileName);

                $result = self::writeFile($path, base64_decode($file->data), $filename);

                if ($result) {
                    $savedFiles[] = $withPath ? $path . $filename : $filename;
                }
            }
        }

        return $savedFiles;
    }

    private static function sanitizeFilename(string $filename, $newFileName): string
    {
        $info = pathinfo($filename);

        $name = ($newFileName !== null)
            ? self::sanitizeFilenamePart($newFileName)
            : self::sanitizeFilenamePart($info['filename']);

        $extension = isset($info['extension']) ? self::sanitizeFilenamePart($info['extension']) : '';

        return ($name !== '' ? $name : '_') . '.' . $extension;
    }

    private static function sanitizeFilenamePart(string $str): string
    {
        return preg_replace("/[^a-zA-Z0-9\s]/", '', $str);
    }

    private static function writeFile(string $path, string $data, string $filename): bool
    {
        $handle = fopen($path . DIRECTORY_SEPARATOR . $filename, 'wb');
        $result = fwrite($handle, $data);

        fclose($handle);

        return $result !== false;
    }
}

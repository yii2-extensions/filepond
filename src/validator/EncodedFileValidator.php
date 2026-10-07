<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\validator;

use InvalidArgumentException;
use Yii;
use yii\validators\Validator;
use yii2\extensions\filepond\file\EncodedFile;
use yii2\extensions\filepond\FilePond;

use function array_map;
use function count;
use function fnmatch;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function preg_split;
use function strtolower;
use function trim;

/**
 * Validates FilePond File Encode payloads by inspecting the decoded content on the server.
 *
 * Extensions are checked against the client file name, MIME types against the bytes through `finfo`, and sizes
 * against the decoded content, so client-side plugin settings are never trusted.
 */
final class EncodedFileValidator extends Validator
{
    /**
     * @var list<string>|string|null Allowed lowercase extensions, as a list or a comma-separated string, or `null`
     * to accept any extension.
     */
    public array|string|null $extensions = null;
    /**
     * Maximum number of files, or `0` for no limit.
     */
    public int $maxFiles = 1;
    /**
     * Maximum file size in bytes, or `null` for no limit.
     */
    public int|null $maxSize = null;
    /**
     * @var list<string>|string|null Allowed MIME types with optional wildcards such as `image/*`, or `null` to
     * accept any type.
     */
    public array|string|null $mimeTypes = null;
    /**
     * Minimum file size in bytes, or `null` for no limit.
     */
    public int|null $minSize = null;
    /**
     * Error message for files larger than {@see}. Supports `{file}` and `{formattedLimit}`;
     * empty for the Yii default.
     */
    public string $tooBig = '';
    /**
     * Error message for more files than {@see}. Supports `{limit}`; empty for the Yii default.
     */
    public string $tooMany = '';
    /**
     * Error message for files smaller than {@see}. Supports `{file}` and `{formattedLimit}`;
     * empty for the Yii default.
     */
    public string $tooSmall = '';
    /**
     * Error message for a required attribute without files; empty for the Yii default.
     */
    public string $uploadRequired = '';
    /**
     * Error message for a disallowed extension. Supports `{file}` and `{extensions}`; empty for the Yii
     * default.
     */
    public string $wrongExtension = '';
    /**
     * Error message for a disallowed MIME type. Supports `{file}` and `{mimeTypes}`; empty for the Yii
     * default.
     */
    public string $wrongMimeType = '';

    public function init(): void
    {
        parent::init();

        $this->extensions = self::normalizeList($this->extensions);
        $this->mimeTypes = self::normalizeList($this->mimeTypes);

        $this->message ??= Yii::t(
            FilePond::TRANSLATION_CATEGORY,
            'The uploaded file is not a valid FilePond payload.',
        );

        if ($this->tooBig === '') {
            $this->tooBig = Yii::t(
                'yii',
                'The file "{file}" is too big. Its size cannot exceed {formattedLimit}.',
            );
        }

        if ($this->tooMany === '') {
            $this->tooMany = Yii::t(
                'yii',
                'You can upload at most {limit, number} {limit, plural, one{file} other{files}}.',
            );
        }

        if ($this->tooSmall === '') {
            $this->tooSmall = Yii::t(
                'yii',
                'The file "{file}" is too small. Its size cannot be smaller than {formattedLimit}.',
            );
        }

        if ($this->uploadRequired === '') {
            $this->uploadRequired = Yii::t(
                'yii',
                'Please upload a file.',
            );
        }

        if ($this->wrongExtension === '') {
            $this->wrongExtension = Yii::t(
                'yii',
                'Only files with these extensions are allowed: {extensions}.',
            );
        }

        if ($this->wrongMimeType === '') {
            $this->wrongMimeType = Yii::t(
                'yii',
                'Only files with these MIME types are allowed: {mimeTypes}.',
            );
        }
    }

    /**
     * @param mixed $value Request value of the FilePond input.
     */
    public function isEmpty($value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if ($item !== null && $item !== '') {
                    return false;
                }
            }

            return true;
        }

        return $value === null || $value === '';
    }

    /**
     * @param mixed $value Request value of the FilePond input.
     *
     * @return array{0: string, 1: array<string, mixed>}|null Error message and parameters, or `null` when valid.
     */
    protected function validateValue($value): array|null
    {
        try {
            $files = EncodedFile::fromInput($value);
        } catch (InvalidArgumentException) {
            return [is_string($this->message) ? $this->message : '', []];
        }

        if ($files === []) {
            return [$this->uploadRequired, []];
        }

        if ($this->maxFiles > 0 && count($files) > $this->maxFiles) {
            return [$this->tooMany, ['limit' => $this->maxFiles]];
        }

        foreach ($files as $file) {
            $error = $this->validateFile($file);

            if ($error !== null) {
                return $error;
            }
        }

        return null;
    }

    /**
     * @param list<string> $patterns Allowed MIME type patterns.
     */
    private static function matchesMimeType(string $mimeType, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $mimeType, FNM_CASEFOLD)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string>|string|null $value List, comma-separated string, or `null`.
     *
     * @return list<string>|null Lowercase, trimmed entries, or `null`.
     */
    private static function normalizeList(array|string|null $value): array|null
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = preg_split('/[\s,|]+/', $value, -1, PREG_SPLIT_NO_EMPTY);
            $value = $value === false ? [] : $value;
        }

        return array_map(static fn(string $item): string => strtolower(trim($item)), $value);
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}|null
     */
    private function validateFile(EncodedFile $file): array|null
    {
        $size = $file->getSize();

        if ($this->maxSize !== null && $size > $this->maxSize) {
            return [
                $this->tooBig,
                [
                    'file' => $file->name,
                    'limit' => $this->maxSize,
                    'formattedLimit' => Yii::$app->formatter->asShortSize($this->maxSize),
                ],
            ];
        }

        if ($this->minSize !== null && $size < $this->minSize) {
            return [
                $this->tooSmall,
                [
                    'file' => $file->name,
                    'limit' => $this->minSize,
                    'formattedLimit' => Yii::$app->formatter->asShortSize($this->minSize),
                ],
            ];
        }

        if (is_array($this->extensions) && !in_array($file->getExtension(), $this->extensions, true)) {
            return [
                $this->wrongExtension,
                ['file' => $file->name, 'extensions' => implode(', ', $this->extensions)],
            ];
        }

        if (is_array($this->mimeTypes) && !self::matchesMimeType($file->getMimeType(), $this->mimeTypes)) {
            return [
                $this->wrongMimeType,
                ['file' => $file->name, 'mimeTypes' => implode(', ', $this->mimeTypes)],
            ];
        }

        return null;
    }
}

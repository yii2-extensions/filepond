<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\file;

use finfo;
use InvalidArgumentException;
use JsonException;
use yii2\extensions\filepond\exception\Message;

use function base64_decode;
use function get_debug_type;
use function is_array;
use function is_float;
use function is_int;
use function is_string;
use function json_decode;
use function pathinfo;
use function round;
use function strlen;
use function strtolower;

/**
 * Represents a file submitted by the FilePond File Encode plugin as a base64 JSON payload.
 *
 * @see https://pqina.nl/filepond/docs/api/plugins/file-encode/
 */
final readonly class EncodedFile
{
    /**
     * @param string $name Client file name.
     * @param string $type Client MIME type as reported by the browser.
     * @param array<string, mixed> $metadata FilePond item metadata, such as `crop` or `poster`.
     * @param string $data Decoded binary content.
     */
    private function __construct(
        public string $name,
        public string $type,
        public array $metadata,
        private string $data,
    ) {}

    /**
     * Creates a file from a decoded File Encode payload.
     *
     * @param array<array-key, mixed> $payload Payload with `name` and base64 `data`, plus optional `type` and
     * `metadata`.
     *
     * @throws InvalidArgumentException if a key has an unexpected type or `data` is not valid base64.
     *
     * @return self File with decoded content.
     */
    public static function fromArray(array $payload): self
    {
        $name = $payload['name'] ?? null;
        $type = $payload['type'] ?? '';
        $data = $payload['data'] ?? null;
        $metadata = $payload['metadata'] ?? [];

        if (!is_string($name)) {
            throw new InvalidArgumentException(
                Message::ENCODED_FILE_INVALID_KEY->getMessage('name', 'string'),
            );
        }

        if (!is_string($type)) {
            throw new InvalidArgumentException(
                Message::ENCODED_FILE_INVALID_KEY->getMessage('type', 'string'),
            );
        }

        if (!is_string($data)) {
            throw new InvalidArgumentException(
                Message::ENCODED_FILE_INVALID_KEY->getMessage('data', 'string'),
            );
        }

        if (!is_array($metadata)) {
            throw new InvalidArgumentException(
                Message::ENCODED_FILE_INVALID_KEY->getMessage('metadata', 'array'),
            );
        }

        $binary = base64_decode($data, true);

        if ($binary === false) {
            throw new InvalidArgumentException(
                Message::ENCODED_FILE_INVALID_BASE64->getMessage(),
            );
        }

        /** @var array<string, mixed> $metadata */
        return new self($name, $type, $metadata, $binary);
    }

    /**
     * Creates the files submitted by one FilePond input.
     *
     * Accepts the raw request value: a JSON string for single inputs, a list of JSON strings or decoded payloads
     * for multiple inputs, and `null` or empty values when nothing was selected.
     *
     * @param mixed $input Request value of the FilePond input.
     *
     * @throws InvalidArgumentException if the value or one of its entries is not a valid payload.
     *
     * @return list<self> Decoded files, empty when nothing was submitted.
     */
    public static function fromInput(mixed $input): array
    {
        if ($input === null || $input === '' || $input === []) {
            return [];
        }

        if (is_string($input)) {
            return [self::fromJson($input)];
        }

        if (!is_array($input)) {
            throw new InvalidArgumentException(
                Message::ENCODED_FILE_INVALID_PAYLOAD->getMessage(get_debug_type($input)),
            );
        }

        $files = [];

        foreach ($input as $item) {
            if ($item === null || $item === '') {
                continue;
            }

            $files[] = match (true) {
                is_string($item) => self::fromJson($item),
                is_array($item) => self::fromArray($item),
                default => throw new InvalidArgumentException(
                    Message::ENCODED_FILE_INVALID_PAYLOAD->getMessage(get_debug_type($item)),
                ),
            };
        }

        return $files;
    }

    /**
     * Creates a file from the JSON string submitted by the File Encode plugin.
     *
     * @param string $json JSON object with `name`, `type`, `data`, and optional `metadata`.
     *
     * @throws InvalidArgumentException if the JSON is malformed or is not an object.
     *
     * @return self File with decoded content.
     */
    public static function fromJson(string $json): self
    {
        try {
            $payload = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(
                Message::ENCODED_FILE_INVALID_JSON->getMessage($exception->getMessage()),
                $exception->getCode(),
                $exception,
            );
        }

        if (!is_array($payload)) {
            throw new InvalidArgumentException(
                Message::ENCODED_FILE_INVALID_PAYLOAD->getMessage(get_debug_type($payload)),
            );
        }

        return self::fromArray($payload);
    }

    /**
     * Returns the crop rectangle in source image pixels stored by the Cropper.js editor.
     *
     * @return array{x: int, y: int, width: int, height: int}|null Rectangle, or `null` when the file was not cropped.
     */
    public function getCropRectangle(): array|null
    {
        $crop = $this->metadata['crop'] ?? null;

        $rect = is_array($crop) ? ($crop['rect'] ?? null) : null;

        $values = [];

        foreach (['x', 'y', 'width', 'height'] as $key) {
            $value = is_array($rect) ? ($rect[$key] ?? null) : null;

            if (!is_int($value) && !is_float($value)) {
                return null;
            }

            $values[$key] = (int) round($value);
        }

        if ($values['width'] <= 0 || $values['height'] <= 0) {
            return null;
        }

        return $values;
    }

    /**
     * Returns the decoded binary content.
     *
     * @return string File content.
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * Returns the lowercase extension of the client file name.
     *
     * @return string Extension without the dot, or an empty string.
     */
    public function getExtension(): string
    {
        return strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
    }

    /**
     * Returns the MIME type detected from the content rather than the client-reported {@see $type}.
     *
     * @return string Detected MIME type, or `application/octet-stream` when detection fails.
     */
    public function getMimeType(): string
    {
        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->buffer($this->data);

        return is_string($mimeType) ? $mimeType : 'application/octet-stream';
    }

    /**
     * Returns the content size in bytes.
     *
     * @return int Size in bytes.
     */
    public function getSize(): int
    {
        return strlen($this->data);
    }

    /**
     * Returns a copy with different content, for example after server-side processing.
     *
     * @param string $data New binary content.
     * @param string|null $type New client MIME type, or `null` to keep the current one.
     * @param string|null $name New client file name, or `null` to keep the current one.
     *
     * @return self Copy with the replaced values.
     */
    public function withData(string $data, string|null $type = null, string|null $name = null): self
    {
        return new self($name ?? $this->name, $type ?? $this->type, $this->metadata, $data);
    }
}

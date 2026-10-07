<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\exception;

use function sprintf;

/**
 * Represents error message templates for exceptions thrown by the extension.
 *
 * Use {@see Message::getMessage()} to format a template with `sprintf()` arguments.
 */
enum Message: string
{
    /**
     * Error when cropper options are configured without enabling the Image Edit plugin.
     *
     * Format: "The 'cropper' options require 'allowImageEdit' to be enabled."
     */
    case CROPPER_REQUIRES_IMAGE_EDIT = "The 'cropper' options require 'allowImageEdit' to be enabled.";

    /**
     * Error when the target directory cannot be created or written to.
     *
     * Format: "Directory '%s' is not writable."
     */
    case DIRECTORY_NOT_WRITABLE = "Directory '%s' is not writable.";

    /**
     * Error when the `data` key of an encoded file is not valid base64.
     *
     * Format: "Encoded file 'data' is not valid base64."
     */
    case ENCODED_FILE_INVALID_BASE64 = "Encoded file 'data' is not valid base64.";

    /**
     * Error when an encoded file payload cannot be decoded as JSON.
     *
     * Format: "Encoded file payload is not valid JSON: '%s'."
     */
    case ENCODED_FILE_INVALID_JSON = "Encoded file payload is not valid JSON: '%s'.";

    /**
     * Error when a required key of an encoded file has an unexpected type.
     *
     * Format: "Encoded file key '%s' must be of type '%s'."
     */
    case ENCODED_FILE_INVALID_KEY = "Encoded file key '%s' must be of type '%s'.";

    /**
     * Error when an encoded file payload is neither a JSON object nor an array.
     *
     * Format: "Encoded file payload must be a JSON object or an array; received '%s'."
     */
    case ENCODED_FILE_INVALID_PAYLOAD = "Encoded file payload must be a JSON object or an array; received '%s'.";

    /**
     * Error when a file cannot be written to disk.
     *
     * Format: "Unable to write file '%s'."
     */
    case FILE_WRITE_FAILED = "Unable to write file '%s'.";

    /**
     * Error when server-side cropping is requested without the GD extension.
     *
     * Format: "The 'gd' extension is required for server-side image cropping."
     */
    case GD_EXTENSION_REQUIRED = "The 'gd' extension is required for server-side image cropping.";

    /**
     * Error when image data cannot be decoded by GD.
     *
     * Format: "Unable to decode image data of file '%s'."
     */
    case IMAGE_DECODE_FAILED = "Unable to decode image data of file '%s'.";

    /**
     * Error when a cropped image cannot be encoded to the requested MIME type.
     *
     * Format: "Unable to encode image data of file '%s' as '%s'."
     */
    case IMAGE_ENCODE_FAILED = "Unable to encode image data of file '%s' as '%s'.";

    /**
     * Error when a crop aspect ratio does not follow the `width:height` format.
     *
     * Format: "Crop aspect ratio must use the 'width:height' format with positive numbers; received '%s'."
     */
    case INVALID_CROP_ASPECT_RATIO = "Crop aspect ratio must use the 'width:height' format with positive numbers; "
        . "received '%s'.";

    /**
     * Error when the version of an npm package cannot be read for CDN delivery.
     *
     * Format: "Unable to read the version of npm package '%s' from '%s'."
     */
    case PACKAGE_VERSION_UNAVAILABLE = "Unable to read the version of npm package '%s' from '%s'.";

    /**
     * Returns the formatted message string for the error case.
     *
     * @param int|string ...$argument Values to insert into the message template.
     *
     * @return string Formatted error message with interpolated arguments.
     */
    public function getMessage(int|string ...$argument): string
    {
        return sprintf($this->value, ...$argument);
    }
}

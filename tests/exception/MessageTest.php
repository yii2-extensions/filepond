<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests\exception;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use yii2\extensions\filepond\exception\Message;

/**
 * Unit tests for {@see Message} template formatting.
 */
#[Group('exception')]
final class MessageTest extends TestCase
{
    public function testFormatsTemplateWithArguments(): void
    {
        self::assertSame(
            "Encoded file key 'data' must be of type 'string'.",
            Message::ENCODED_FILE_INVALID_KEY->getMessage('data', 'string'),
            'Arguments must be interpolated in order.',
        );
    }

    public function testReturnsTemplateWithoutArguments(): void
    {
        self::assertSame(
            "Encoded file 'data' is not valid base64.",
            Message::ENCODED_FILE_INVALID_BASE64->getMessage(),
            'Templates without placeholders must be returned verbatim.',
        );
    }
}

<?php

declare(strict_types=1);

namespace Yii\FilePond\Tests;

use RuntimeException;
use Yii;
use Yii\FilePond\FilePond;
use Yii\FilePond\Tests\Support\TestForm;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ExceptionTest extends TestCase
{
    public function testNotSetAttribute(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The attribute is not set.');

        FilePond::widget(['model' => new TestForm()]);
    }

    public function testNotSetModel(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The model is not set.');

        FilePond::widget(['attribute' => 'array']);
    }
}

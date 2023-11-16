<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond\Tests;

use RuntimeException;
use Yii2\Extensions\FilePond\FilePond;
use Yii2\Extensions\FilePond\Tests\Support\TestForm;

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

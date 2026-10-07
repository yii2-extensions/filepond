<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests\support\stub;

use yii\base\Model;

/**
 * Form model with FilePond-backed attributes for widget and validator tests.
 */
final class TestForm extends Model
{
    public mixed $avatar = null;

    public mixed $documents = null;
}

<?php

declare(strict_types=1);

namespace Yii2\Extensions\FilePond;

use PHPForge\Html\Attribute;
use PHPForge\Html\Helper\Utils;
use PHPForge\Html\HtmlBuilder;
use PHPForge\Widget\Element;
use yii\base\Model;

use function array_key_exists;
use function is_string;

/**
 * The input element with a type attribute whose value is "file" represents a list of file items, each consisting of a
 * file name, a file type, and a file body (the contents of the file).
 *
 * @link https://www.w3.org/TR/2012/WD-html-markup-20120329/input.file.html#input.file
 */
final class File extends Element
{
    use Attribute\Custom\HasAttributes;
    use Attribute\Input\CanBeMultiple;
    use Attribute\Input\HasAccept;

    private array $attributes = [];

    public function __construct(
        private readonly Model $formModel,
        private readonly string $attribute,
        array $definitions = [],
    ) {
        parent::__construct($definitions);
    }

    protected function run(): string
    {
        $attributes = $this->attributes;
        $attributes['type'] = 'file';

        if (!array_key_exists('name', $attributes)) {
            $name = Utils::generateInputName($this->formModel->formName(), $this->attribute);
        } else {
            $name = is_string($attributes['name']) ? $attributes['name'] : '';
        }

        $attributes['id'] = Utils::generateInputId($this->formModel->formName(), $this->attribute);
        $attributes['name'] = Utils::generateArrayableName($name);

        // input type="file" not supported value attribute.
        unset($attributes['value']);

        return HtmlBuilder::create('input', '', $attributes);
    }
}

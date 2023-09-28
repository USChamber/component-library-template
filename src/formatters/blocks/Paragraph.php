<?php

namespace USChamber\ComponentLibrary\formatters\blocks;

use USChamber\ComponentLibrary\base\BaseVizyFormatter;
use USChamber\ComponentLibrary\formatters\blocks\traits\PlainTextTrait;

/**
 *
 * @property-read string $componentId
 */
class Paragraph extends BaseVizyFormatter
{
    use PlainTextTrait;

    public static function getFormatterId(): string
    {
        return 'paragraph';
    }

    public function getComponentId(): string
    {
        return '@block:paragraph';
    }
}

<?php

namespace USChamber\ComponentLibrary\formatters\blocks;

use USChamber\ComponentLibrary\base\BaseVizyFormatter;
use USChamber\ComponentLibrary\formatters\blocks\traits\PlainTextTrait;

/**
 *
 * @property-read string $componentId
 */
class Heading extends BaseVizyFormatter
{
    use PlainTextTrait;

    public static function getFormatterId(): string
    {
        return 'heading';
    }

    public function getComponentId(): string
    {
        return '@block:heading';
    }
}

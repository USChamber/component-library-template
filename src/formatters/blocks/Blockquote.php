<?php

namespace USChamber\ComponentLibrary\formatters\blocks;

use USChamber\ComponentLibrary\base\BaseVizyFormatter;
use USChamber\ComponentLibrary\formatters\blocks\traits\QuoteTrait;

/**
 *
 * @property-read string $componentId
 */
class Blockquote extends BaseVizyFormatter
{
    use QuoteTrait;

    public static function getFormatterId(): string
    {
        return 'blockquote';
    }

    public function getComponentId(): string
    {
        return '@block:blockquote';
    }
}

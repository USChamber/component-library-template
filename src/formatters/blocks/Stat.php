<?php

namespace USChamber\ComponentLibrary\formatters\blocks;

use USChamber\ComponentLibrary\base\BaseVizyFormatter;
use USChamber\ComponentLibrary\formatters\blocks\traits\StatTrait;

/**
 *
 * @property-read string $componentId
 */
class Stat extends BaseVizyFormatter
{
    use StatTrait;

    public static function getFormatterId(): string
    {
        return 'stat';
    }

    public function getComponentId(): string
    {
        return '@block:stat';
    }

}

<?php

namespace USChamber\ComponentLibrary\formatters\blocks;

use USChamber\ComponentLibrary\base\BaseVizyFormatter;
use USChamber\ComponentLibrary\formatters\blocks\traits\ListTrait;

/**
 *
 * @property-read string $componentId
 */
class BulletList extends BaseVizyFormatter
{
    use ListTrait;

    public static function getFormatterId(): string
    {
        return 'bulletList';
    }

    public function getComponentId(): string
    {
        return '@block:list';
    }
}

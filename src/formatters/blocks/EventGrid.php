<?php

namespace USChamber\ComponentLibrary\formatters\blocks;

use USChamber\ComponentLibrary\base\BaseVizyFormatter;
use USChamber\ComponentLibrary\formatters\blocks\traits\EventGridTrait;

/**
 *
 * @property-read string $componentId
 */
class EventGrid extends BaseVizyFormatter
{
    use EventGridTrait;

    public static function getFormatterId(): string
    {
        return 'eventGrid';
    }

    public function getComponentId(): string
    {
        $contentCount = $this->data['values']['content']['fields']['contentCount'] ?? '3';
        return '@listing:grid-' . $contentCount;
    }
}

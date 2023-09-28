<?php

namespace USChamber\ComponentLibrary\formatters\blocks;

use USChamber\ComponentLibrary\base\BaseVizyFormatter;

/**
 *
 * @property-read string $componentId
 */
class UnknownBlock extends BaseVizyFormatter
{
    public static function getFormatterId(): string
    {
        return 'unknownBlock';
    }

    public function getComponentId(): string
    {
        return '@block:unknown-block';
    }

    public function getData(): array
    {
        if (!is_array($this->data)) {
            $returnObj = [
                'data' => $this->data,
            ];
        }
        $returnObj['data'] = $this->data;
        $returnObj['type'] = $this->type;
        $returnObj['context'] = $this->context;

        return $returnObj;
    }
}

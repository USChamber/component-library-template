<?php

namespace USChamber\ComponentLibrary\formatters\blocks;

use USChamber\ComponentLibrary\base\BaseVizyFormatter;
use USChamber\ComponentLibrary\helpers\VizyHelper;

/**
 *
 * @property-read string $componentId
 */
class AccordionBlock extends BaseVizyFormatter
{

    public array $items = [];
    public bool $firstAccordionOpen = false;

    public function init(): void
    {
        $data = $this->data;
        $items = $data['values']['content']['fields']['accordionMatrix'] ?? [];
        $this->firstAccordionOpen = $data['values']['content']['fields']['featureEnabled_DefaultOff'] ?? false;
        foreach ($items as $block) {
            $this->items[] = [
                'title' => $block['fields']['header'],
                'content' => $block['fields']['description'],
            ];
        }
    }

    public static function getFormatterId(): string
    {
        return 'accordionBlock';
    }

    public function getComponentId(): string
    {
        return '@block:accordion';
    }

    public function getData(): array
    {
        $items = [];
        $loopIndex = 0;

        foreach ($this->items as $index => $item) {
            $items[] = ['title' => $item['title'], 'isOpen' => $index === 0 && $this->firstAccordionOpen, 'blocks' =>
                []];
            foreach ($item['content'] as $element) {
                $type = $element['type'];
                if ($type === 'paragraph') {
                    $text = VizyHelper::transformVizyText($element['content']);
                    $items[$loopIndex]['blocks'][] = ['type' => '@block:paragraph', 'data' => ['text' => $text]];
                } elseif ($type === 'bulletList') {
                    $items[$loopIndex]['blocks'][] = VizyHelper::formatList($element, $type, true);
                }
            }
            $loopIndex++;
        }

        return [
            'items' => $items,
        ];
    }
}

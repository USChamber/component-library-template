<?php

namespace USChamber\ComponentLibrary\formatters\blocks\traits;

trait StatTrait
{
    public function getData(): array
    {
        $data = $this->data;

        $stats = [];

        $type = $this->type;

        $stats['fullWidth'] = $type === 'fullWidthStat' ? true : false;

        if ($type === 'fullWidthStat') {
            $stats['items'][] = [
                'stat' => $data['values']['content']['fields']['textField'],
                'text' => $data['values']['content']['fields']['quoteAttribution'],
            ];
        } else {
            $blocks = $data['values']['content']['fields']['doubleTextMatrix'];
            foreach ($blocks as $block) {
                $stats['items'][] = [
                    'stat' => $block['fields']['text1'],
                    'text' => $block['fields']['text2'],
                ];
            }
        }

        return $stats;
    }
}

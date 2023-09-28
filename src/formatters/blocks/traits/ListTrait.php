<?php

namespace USChamber\ComponentLibrary\formatters\blocks\traits;

use USChamber\ComponentLibrary\helpers\VizyHelper;

trait ListTrait
{
    public function getData(): array
    {
        $data = $this->data;

        $items = [];
        if (isset($data['rawNode']['content'])) {
            $items = VizyHelper::formatList($data['rawNode'], $this->type);
        } elseif (isset($data['content'])) {
            foreach ($data['content'] as $item) {
                $items = VizyHelper::formatList($data, $this->type);
            }
        }

        return [
            'items' => $items,
        ];
    }
}

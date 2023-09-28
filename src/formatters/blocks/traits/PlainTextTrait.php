<?php

namespace USChamber\ComponentLibrary\formatters\blocks\traits;

use USChamber\ComponentLibrary\helpers\VizyHelper;

trait PlainTextTrait
{
    public function getData(): array
    {
        $data = $this->data;

        $headingOrText = [];

        if (isset($data['rawNode']['content'])) {
            $headingOrText['text'] = VizyHelper::transformVizyText($data['rawNode']['content']);
        } elseif (!empty($data['content'])) {
            $headingOrText['text'] = VizyHelper::transformVizyText($data['content']);
        } elseif (isset($data['rawNode'])) {
            $rawData[] = $data['rawNode'];
            $headingOrText['text'] = VizyHelper::transformVizyText($rawData);
        }

        return $headingOrText;
    }
}

<?php

namespace USChamber\ComponentLibrary\formatters\blocks;

use USChamber\ComponentLibrary\base\BaseVizyFormatter;

/**
 *
 * @property-read string $componentId
 */
class Video extends BaseVizyFormatter
{

    public ?string $videoId = null;
    public string $embedType = 'youtube';
    public ?string $caption = null;

    public function init(): void
    {
        $fields = $this->data['values']['content']['fields'];
        $this->videoId = $fields['videoid'] ?? $fields['videoId'] ?? null;
        $this->embedType = $fields['embedType'] ?? $this->embedType;
        $this->caption = $fields['quoteAttribution'] ?? $fields['caption'] ?? null;
    }

    public static function getFormatterId(): string
    {
        return 'video';
    }

    public function getComponentId(): string
    {
        return '@block:video';
    }

    public function getData(): array
    {
        $video = [];

        $video['videoId'] = $this->videoId;
        $video['type'] = $this->embedType;
        $video['caption'] = $this->caption;

        return $video;
    }
}

<?php

namespace USChamber\ComponentLibrary\formatters\blocks;

use craft\elements\Asset;
use USChamber\ComponentLibrary\base\BaseVizyFormatter;

/**
 *
 * @property-read string $componentId
 */
class Image extends BaseVizyFormatter
{

    public ?Asset $imageElement = null;
    public ?string $imageSource = null;
    public ?string $altText = null;
    public ?string $url = null;
    public ?string $target = null;

    public function init(): void
    {
        parent::init();
        $imageId = $this->data['attrs']['id'] ?? null;
        if ($imageId !== null) {
            $image = Asset::find()
                ->id($imageId);
            if ($image !== null) {
                $this->imageElement = $image->one();
            }
        }
        $this->imageSource = $this->data['attrs']['src'] ?? null;
        $this->altText = $this->data['attrs']['alt'] ?? $this->data['attrs']['altText'] ?? null;
        $this->url = $this->data['attrs']['url'] ?? null;
        $this->target = $this->data['attrs']['target'] ?? null;
    }

    public static function getFormatterId(): string
    {
        return 'image';
    }

    public function getComponentId(): string
    {
        return '@block:image';
    }

    public function getData(): array
    {
        $image['element'] = $this->imageElement;
        $image['src'] = $this->imageSource;
        $image['ratio'] = '';
        $image['alt'] = $this->altText;
        $image['url'] = $this->url;
        $image['target'] = $this->target;

        return $image;
    }
}

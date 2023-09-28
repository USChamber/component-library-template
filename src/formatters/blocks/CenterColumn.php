<?php

namespace USChamber\ComponentLibrary\formatters\blocks;

use USChamber\ComponentLibrary\base\BaseVizyFormatter;
use USChamber\ComponentLibrary\helpers\VizyHelper;
use yii\base\ExitException;

/**
 *
 * @property-read string $componentId
 */
class CenterColumn extends BaseVizyFormatter
{

    public array $content = [];

    public static function getFormatterId(): string
    {
        return 'centerColumn';
    }

    public function getComponentId(): string
    {
        return '@layout:center-column';
    }

    public function init(): void
    {
        $this->content = $this->data['content'] ?? $this->data['values']['content']['fields']['landingPageVizy'];
    }

    /**
     * @throws ExitException
     */
    public function getData(): array
    {

        return [
            'blocks' => VizyHelper::getComponentBlocks(
                $this->content,
                $this->entry,
                true
            ),
        ];
    }
}

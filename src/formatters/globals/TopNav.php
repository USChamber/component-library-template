<?php

namespace USChamber\ComponentLibrary\formatters\globals;

use USChamber\ComponentLibrary\base\BaseFormatter;

/**
 *
 * @property-read string $componentId
 */
class TopNav extends BaseFormatter
{
    public string $searchAction = '/search/';

    public function init(): void
    {
        $this->searchAction = $this->data['values']['content']['fields']['searchAction'] ?? '';
    }

    public static function getFormatterId(): string
    {
        return 'top-nav';
    }

    public function getComponentId(): string
    {
        return '@global:header';
    }

    public function getData(): array
    {
        return [
            'searchAction' => $this->searchAction,
            'navigation' => [],
            'dropdowns' => [],
            'accountLinks' => [],
            'featuredLink' => [],
        ];
    }
}
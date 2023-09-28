<?php

namespace USChamber\ComponentLibrary\formatters\globals;

use USChamber\ComponentLibrary\base\BaseFormatter;

/**
 *
 * @property-read string $componentId
 */
class FooterNav extends BaseFormatter
{
    public string $featuredTitle = 'Featured';
    public string $featuredViewAllTitle = 'View All';
    public string $featuredViewAllHref = '#';
    public array $featuredLinks = [];
    public array $secondaryLinks = [];
    public array $legalLinks = [];

    public function init(): void
    {
        $this->featuredTitle = $this->data['values']['content']['fields']['featuredTitle'] ?? 'Featured';
        $this->featuredViewAllTitle = $this->data['values']['content']['fields']['featuredViewAllTitle'] ?? 'View All';
        $this->featuredViewAllHref = $this->data['values']['content']['fields']['featuredViewAllHref'] ?? '#';
        $this->featuredLinks = $this->data['values']['content']['fields']['featuredLinks'] ?? [];
        $this->secondaryLinks = $this->data['values']['content']['fields']['secondaryLinks'] ?? [];
        $this->legalLinks = $this->data['values']['content']['fields']['legalLinks'] ?? [];
    }

    public static function getFormatterId(): string
    {
        return 'footer-nav';
    }

    public function getComponentId(): string
    {
        return '@global:footer';
    }

    public function getData(): array
    {
        return [
            'copyright' => '&copy;' . date('Y') . ' My Company',
            'featuredLinks' => [
                'title' => $this->featuredTitle,
                'viewAll' => [
                    'title' => $this->featuredViewAllTitle,
                    'href' => $this->featuredViewAllHref,
                ],
                'links' => $this->featuredLinks,
            ],
            'links' => $this->secondaryLinks,
            'legals' => $this->legalLinks,
        ];
    }
}
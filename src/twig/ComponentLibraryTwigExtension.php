<?php

namespace USChamber\ComponentLibrary\twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;
use USChamber\ComponentLibrary\base\Formatters;
use USChamber\ComponentLibrary\helpers\VizyHelper;

class ComponentLibraryTwigExtension extends AbstractExtension implements GlobalsInterface
{
    public function getGlobals(): array
    {
        return [
            'formatter' => new Formatters(),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('parseAsComponents', [
                VizyHelper::class, 'getComponentBlocks',
            ]),
            new TwigFunction('getComponentLibraryKey', function() {
                return getenv('COMPONENT_LIBRARY_VIEW_KEY');
            }),
        ];
    }
}

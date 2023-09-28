<?php

namespace USChamber\ComponentLibrary\base;

use Twig\Markup;

interface FormatterInterface
{
    public static function getFormatterId(): string;

    public function getComponentId(): string;

    public function getData(): array;

    public function getHtml(): Markup;
}
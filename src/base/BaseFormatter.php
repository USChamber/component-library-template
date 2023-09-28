<?php

namespace USChamber\ComponentLibrary\base;

use Craft;
use craft\base\Model;
use craft\helpers\Template;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;

/**
 *
 * @property-read Markup $html
 */
abstract class BaseFormatter extends Model implements FormatterInterface
{
    public mixed $data = null;

    public function getData(): array
    {
        return [];
    }

    /**
     * @throws SyntaxError
     * @throws Exception
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function getHtml(): Markup
    {
        $html = Craft::$app->getView()->renderTemplate($this->getComponentId(), $this->getData());

        return Template::raw($html);
    }

    /**
     * @throws \JsonException
     */
    protected function getDataFromLinkField($fieldData): ?array
    {
        // if $fieldData is not a string, return it as is
        if (!is_string($fieldData)) {
            return $fieldData;
        }

        $decoded = json_decode($fieldData, true, 5, JSON_THROW_ON_ERROR);

        
        $decodedPayload = json_decode($decoded['payload'] ?? 'null', true, 512, JSON_THROW_ON_ERROR);
        $target = $decodedPayload['target'] ?? '';
        $customText = $decodedPayload['customText'] ?? '';

        return [
            'type' => $decoded['type'] ?? '',
            'value' => $decoded['linkedUrl'] ?? $decoded['linkedId'] ?? '',
            'customText' => $customText,
            'target' => $target,
            'enabled' => $decoded['enabled'] ?? true,
        ];
    }
}
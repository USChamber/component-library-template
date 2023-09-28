<?php

namespace USChamber\ComponentLibrary\formatters\blocks\traits;

use Craft;
use craft\helpers\StringHelper;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use USChamber\ComponentLibrary\ComponentLibrary;
use USChamber\ComponentLibrary\helpers\VizyHelper;
use yii\web\NotFoundHttpException;

trait QuoteTrait
{
    /**
     * @throws SyntaxError
     * @throws NotFoundHttpException
     * @throws LoaderError
     */
    public function getData(): array
    {
        $data = $this->data;

        $formatterService = ComponentLibrary::getInstance()->formatters;

        $quote = [];

        if (isset($data['values']['content']['fields']) && $data['enabled'] === true) {
            $quote['quote'] = $data['values']['content']['fields']['quoteText'];
            $quote['attribution'] = $data['values']['content']['fields']['quoteAttribution'];
        } elseif (isset($data['rawNode']) && is_array($data['rawNode'])) {
            $quote['quote'] = [];
            foreach ($data['rawNode']['content'] as $line) {
                if (isset($line['content']) && $line['content'][0]['type'] === 'text') {
                    $quote['quote'][] = VizyHelper::transformVizyText($line['content']);
                } elseif (isset($line['content']) && $line['content'][0]['type'] === 'listItem') {
                    $formatterId = StringHelper::toPascalCase($line['type']);
                    $listInstance = $formatterService->getFormatter($formatterId, [
                        'data' => $line,
                    ]);
                    $items = $listInstance->getData();
                    $twigVariables = ['items' => $items['items']];
                    $template = '{% include "' . $listInstance->getComponentId() . '" with { items: items} %}';
                    $html = Craft::$app->getView()->renderString($template, $twigVariables);
                    $quote['quote'][] = $html;
                }
            }
        } else {
            $quote['quote'] = '';
            foreach ($data['content'] as $line) {
                if (isset($line['content'])) {
                    $quote['quote'] .= VizyHelper::transformVizyText($line['content']);
                }
            }
        }

        return $quote;
    }
}

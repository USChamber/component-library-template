<?php

namespace USChamber\ComponentLibrary\base;

use Craft;
use craft\elements\Category;

abstract class BaseListingFilterInteractive extends BaseListingFilter
{
    abstract public function getFilterName(): string;

    abstract public function getFirstOption(): string;

    abstract public function createInteractiveFilter($listingClass, $ignoreContentCheck): array;

    protected function _getFieldValues($fieldHandle)
    {
        $cacheKey = $fieldHandle;
        $cacheDuration = 60 * 60 * 24; // 1 day
        if (Craft::$app->cache->exists($cacheKey)) {
            return json_decode(Craft::$app->cache->get($cacheKey), true, 512, JSON_THROW_ON_ERROR);
        }
        $data = Craft::$app->getFields()->getFieldByHandle($fieldHandle)->options;
        Craft::$app->getCache()->set($cacheKey, json_encode($data, JSON_THROW_ON_ERROR), $cacheDuration);

        return $data;
    }

    protected function _getCategories($categoryGroup)
    {
        $cacheKey = $categoryGroup;
        $cacheDuration = 60 * 60 * 24; // 1 day
        if (Craft::$app->cache->exists($cacheKey)) {
            return json_decode(Craft::$app->cache->get($cacheKey), true, 512, JSON_THROW_ON_ERROR);
        }
        $data = Category::find()
            ->group($categoryGroup)
            ->all();
        Craft::$app->getCache()->set($cacheKey, json_encode($data), $cacheDuration);

        return $data;
    }

    protected function createDropdownValues(array $elements, $listingClass, bool $sorted = false, bool $ignoreContentCheck = false, $preselectedValue = null): array
    {
        $preselected = $preselectedValue ?? $this->getValues()[0] ?? null;

        $values = [];
        foreach ($elements as $element) {
            if (is_array($element)) {
                if (isset($element['optgroup'])) {
                    continue;
                }
                $value = [
                    'label' => $element['label'] ?? $element['title'],
                    'value' => $element['value'] ?? $element['id'],
                ];
            } else {
                $value = [
                    'label' => $element->title ?? $element->label,
                    'value' => $element->id ?? $element->value,
                ];
            }
            if ((string)$preselected == (string)$value['value']) {
                $value['selected'] = true;
            }
            if ($ignoreContentCheck) {
                $values[] = $value;
            } elseif ($listingClass->checkFilterForContent($this->getFilterId(), $value['value'])) {
                $values[] = $value;
            }
        }
        if ($sorted) {
            usort($values, static function($val1, $val2) {
                return $val1['label'] <=> $val2['label'];
            });
        }

        return $values;
    }
}
<?php

namespace USChamber\ComponentLibrary\base;

use Craft;

abstract class BaseListingFilter
{

    public const COMBINING_OPERATOR_AND = 'and';
    public const COMBINING_OPERATOR_OR = 'or';

    protected array $values = [];

    public function __construct($values = [], $tabIndex = null)
    {
        $this->setValues($values, $tabIndex);
    }

    public function getCombiningOperator(): string
    {
        return self::COMBINING_OPERATOR_AND;
    }

    public function setValues(array $values, $tabIndex = null): void
    {
        if (!empty($values)) {
            $this->values = array_merge($this->values, $values);
        }

        if ($this instanceof BaseListingFilterInteractive) {
            $urlValues = $this->getValuesFromRequest($tabIndex);
            $this->values = array_merge($this->values, $urlValues);
        }
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function getValuesFromRequest($tabIndex = null): array
    {
        $request = Craft::$app->request;
        if ($tabIndex !== null && $tabIndex !== (int)$request->getParam('tab')) {
            return [];
        }

        $filters = [];
        $filterId = $this->getFilterId();
        $filterValue = $request->getParam($filterId);
        if ($filterValue) {
            $filters[] = $filterValue;
        }

        return $filters;
    }

    abstract public function getFilterId(): string;

    abstract public function getFilterCriteria($values = []): array;
}
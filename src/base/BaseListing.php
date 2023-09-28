<?php

namespace USChamber\ComponentLibrary\base;

use Craft;
use craft\elements\db\EntryQuery;
use craft\elements\Entry;

abstract class BaseListing
{
    abstract protected function getFeedId(): string;

    abstract protected function formatCardObj($feedElement): array;

    // things that children need to modify
    protected string $feedId;
    protected ?Entry $element;
    protected array $defaultSections = ['editorial', 'comms', 'policy', 'report'];

    protected string $title = 'Latest Content';
    protected string $orderBy = 'postDate desc';
    protected int $page;
    protected string $noContentMessage = 'There is no content for this search. Please update your filters and try again.';
    protected int $itemsPerPage = 10;
    protected ?int $tabIndex = null;
    protected bool $removePagination = false;
    protected bool $hideIfNoResults = false;
    protected bool $useVideoBioUrl = false;
    protected string $layout = 'row';
    protected array $staticFilters = [];
    protected array $interactiveFilters = [];
    protected bool $isAjax = false;
    protected ?string $elementId;
    protected string $relatedToOperator = 'and';
    protected string $interactiveFilterLayout = 'horizontal';
    protected array $withQuery = ['image'];
    protected array $emptyFieldsCheck = [];
    protected bool $ignoreContentCheck = false;
    protected bool $runQueryOnChange = true;
    protected int $cacheDuration = 60 * 60 * 24; // 1 day
    protected string $beginningOfTime = '2017-01-01';

    // chrome to make the feed work
    private array $pages = [];
    private array $buttons = [];
    private string $pagelessCacheKey = '';
    private string $cacheKey = '';
    private array $interactiveFilterComponents = [];
    private array $feedElements;
    private array $feedItems = [];
    private array $filters = [];
    private string $className;

    public function __construct($elementId = null)
    {
        $this->elementId = $elementId;
        $this->page = Craft::$app->request->getQueryParam('page') ?? 1;
        $this->feedId = $this->getFeedId();

        $this->_initContentFilters($this->staticFilters, $this->interactiveFilters);
        $this->_initCacheKeys($this->feedId);

        $this->feedElements = $this->_getFeedElements($this->filters);
        $this->_initInteractiveFilters($this->interactiveFilters);

        // set the class name so the javascript knows which feed this is
        $classArr = explode('\\', get_class($this));
        $this->className = $classArr[count($classArr) - 1];
    }

    private function _initQuery($orderBy, $offset, $itemsPerPage, $emptyFieldsCheckQueryArray): EntryQuery
    {
        $query = Entry::find();
        $query->section($this->defaultSections);
        $query->pageHidden(false);
        $query->postDate('>= ' . $this->beginningOfTime);
        $query->with($this->withQuery);
        $query->orderBy($orderBy);
        $query->offset($offset);
        $query->limit($itemsPerPage);

        foreach ($emptyFieldsCheckQueryArray as $field) {
            $query->$field(':notempty:');
        }

        return $query;
    }

    /**
     * Takes all filters and creates unique cache keys
     */
    private function _initCacheKeys($feedId): void
    {
        $this->cacheKey = $feedId;

        foreach ($this->filters as $filter) {
            $this->cacheKey .= '_' . $filter->getFilterId() . ':' . json_encode($filter->getFilterCriteria(), JSON_THROW_ON_ERROR);
        }

        $this->pagelessCacheKey = base64_encode($this->cacheKey);
        $this->cacheKey = base64_encode($this->cacheKey . '_' . $this->page);
    }

    private function _initContentFilters($staticFilters, $interactiveFilters): void
    {
        foreach ($staticFilters as $filter) {
            $this->filters[] = $filter;
        }
        foreach ($interactiveFilters as $filter) {
            $this->filters[] = $filter;
        }
    }

    private function _initInteractiveFilters(): void
    {
        foreach ($this->interactiveFilters as $filter) {
            foreach ($filter->createInteractiveFilter($this, $this->ignoreContentCheck) as $filterComponent) {
                $this->interactiveFilterComponents[] = $filterComponent;
            }
        }
    }

    public function getBlocks(): array
    {

        $this->_createDisplay();

        if ((count($this->feedItems) === 0 && $this->isAjax) || (count($this->feedItems) === 0 && !$this->hideIfNoResults)) {
            $this->feedItems[] = [
                'type' => '@block:paragraph',
                'data' => [
                    'text' => $this->noContentMessage,
                ],
            ];
        }

        $filterComponents = $this->interactiveFilterComponents;

        if (!$this->runQueryOnChange) {
            $filterComponents[] = [
                'type' => '@atom:btn',
                'data' => [
                    'label' => 'Apply',
                    'customClasses' => 'w-full',
                    'attrs' => [
                        'data-filterblock-apply' => null,
                    ],
                ],
            ];
            $filterComponents[] = [
                'type' => '@atom:btn',
                'data' => [
                    'label' => 'Reset',
                    'variation' => 'secondary',
                    'customClasses' => 'w-full',
                    'attrs' => [
                        'data-filterblock-reset' => null,
                    ],
                ],
            ];
        }

        $listing = [
            [
                'type' => '@behavior-block:filter-select',
                'data' => [
                    'attrs' => [
                        'data-pageless-cache-key' => $this->pagelessCacheKey,
                        'data-cache-key' => $this->cacheKey,
                    ],
                    'bar' => [
                        'title' => $this->title,
                        'id' => $this->pagelessCacheKey,
                    ],
                    'filterComponents' => $filterComponents,
                    'filterLayout' => $this->interactiveFilterLayout,
                    'rowList' => [
                        'type' => '@listing:' . $this->layout,
                        'data' => [
                            'items' => $this->feedItems,
                            'attrs' => [
                                'data-filterblock-content-feed' => null,
                                'data-feed-id' => $this->feedId,
                                'data-action-url' => Craft::getAlias('@web') . '/ajax/uscc/rowlisting"',
                            ],
                        ],
                    ],
                    'uniqueId' => $this->feedId,
                ],
            ],
        ];

        if (!$this->removePagination) {
            $listing[] = [
                'type' => '@block:pagination',
                'data' => [
                    'next' => $this->buttons['next'],
                    'prev' => $this->buttons['prev'],
                    'pages' => $this->pages,
                ],
            ];
        }

        if (count($this->feedItems) > 0) {
            $attrs = [
                'data-content-feed-wrapper' => null,
                'data-behavior' => 'filterBlock',
                'data-element-id' => $this->elementId,
                'data-php-class' => $this->className,
                'data-run-query-on-change' => $this->runQueryOnChange ? 'true' : 'false',
            ];
            if ($this->tabIndex !== null) {
                $attrs['data-filterblock-tab-index'] = $this->tabIndex;
            }

            return [
                [
                    'type' => '@repeater:blocks',
                    'data' => [
                        'items' => $listing,
                        'attrs' => $attrs,
                    ],
                ],
            ];
        }

        return [];
    }

    private function _createDisplay(): void
    {
        foreach ($this->feedElements['elements'] as $feedElement) {
            $this->feedItems[] = $this->formatCardObj($feedElement);
        }

        $pageCount = ceil($this->feedElements['total'] / $this->itemsPerPage);
        $this->pages = $this->_createPagesArray($pageCount);
        $this->buttons = $this->_createPagerButtonObj($pageCount);
    }

    private function _isCombinedQuery(string $queryHandle): bool
    {
        $combinedQueryHandles = [
            'relatedTo',
            'section',
            'caseUpdates',
        ];

        return in_array($queryHandle, $combinedQueryHandles, true);
    }

    private function _getFeedElements(array $filters, array $options = []): array
    {

        $offset = ($this->page - 1) * $this->itemsPerPage;
        $query = $this->_initQuery($this->orderBy, $offset, $this->itemsPerPage, $this->emptyFieldsCheck);

        // handle filtering based off filters passed in
        $combinedQueries = [];
        $standaloneQueries = [];
        // handle static filters

        foreach ($filters as $filter) {
            $values = (isset($options['filter']) && $options['filter'] === $filter->getFilterId()) ? $options['values'] : [];
            $criteria = $filter->getFilterCriteria($values);
            foreach ($criteria as $criterion) {
                if ($this->_isCombinedQuery($criterion['query'])) {
                    $operator = $filter->getCombiningOperator();
                    if ($operator === 'and') {
                        if (!isset($combinedQueries[$criterion['query']])) {
                            $combinedQueries[$criterion['query']] = $criterion['value'];
                        } else {
                            $combinedQueries[$criterion['query']] = array_intersect($combinedQueries[$criterion['query']], $criterion['value']);
                        }
                    } else {
                        if (!isset($combinedQueries[$criterion['query']])) {
                            $combinedQueries[$criterion['query']] = [];
                        }
                        $combinedQueries[$criterion['query']][] = $criterion['value'];
                    }
                } else {
                    $standaloneQueries[] = ['query' => $criterion['query'], 'value' => $criterion['value']];
                }
            }
        }

        foreach ($standaloneQueries as $criterion) {
            $query->{$criterion['query']}($criterion['value']);
        }

        foreach ($combinedQueries as $criterion => $value) {
            if ($criterion === 'search' && count($value) > 0) {
                $value = $value[0];
            }
            if ($criterion === 'relatedTo' && count($value) > 1) {
                array_unshift($value, $this->relatedToOperator);
            }
            $query->$criterion($value);
        }

        if (isset($_GET['debug'], $_GET['format']) && $_GET['debug'] === 'query' && $_GET['format'] === 'sql') {
            Craft::dd($query->getRawSql());
        }

        if (isset($_GET['debug']) && $_GET['debug'] === 'query') {
            Craft::dd($query);
        }

        if (isset($options['countOnly'])) {
            return [
                'total' => $query->count() ?? 0,
                'options' => $options,
            ];
        }

        $elements = $query->all();

        $data = [
            'total' => $query->count() ?? 0,
            'elements' => $elements ?? [],
        ];

        return $data;
    }

    /**
     * Creates an array of buttons based off the total number of pages. Returns
     * something like this: [1],2,3,...10, or 1...6,7,[8],9,10, or 1,2,[3],4,5,...10
     *
     * @return array An array of buttons for each page, including a disabled ellipsis button
     */
    private function _createPagesArray($pageCount): array
    {
        $bufferSize = 3;

        // only render buttons for between these, also first and last
        $bufferMin = ($this->page - $bufferSize > 0) ? $this->page - $bufferSize : 1;
        // current page number falls directly between these
        $bufferMax = ($this->page - $bufferSize <= $pageCount) ? $this->page + $bufferSize : $pageCount;

        for ($i = 1; $i <= $pageCount; $i++) {

            $containedInBuffer = ($i <= $bufferMax && $i >= $bufferMin);
            $firstOrLast = ($i === 1 || $i === $pageCount);
            $prevBuffer = ($i + 1 === $bufferMin);
            $afterBuffer = ($i - 1 === $bufferMax);

            if ($i === $this->page) {
                $this->pages[] = [
                    'label' => $i,
                    'disabled' => true,
                ];
            } elseif ($firstOrLast || $containedInBuffer) {
                $this->pages[] = [
                    'label' => $i,
                    'href' => $this->_updateUrlParam('page', $i) . '#' . $this->pagelessCacheKey,
                ];
            } elseif ($prevBuffer || $afterBuffer) {
                $this->pages[] = [
                    'disabled' => true,
                ];
            }
        }

        return $this->pages;
    }

    /**
     * Creates the pager buttons (next/previous) for the bottom of the feed
     *
     * @return array An array of the buttons ready for fractal
     */
    private function _createPagerButtonObj($pageCount): array
    {

        if ($this->page == 1 || $this->page > $pageCount) {
            $prevButton = [
                'disabled' => true,
            ];
        } else {
            $url = $this->_updateUrlParam('page', $this->page - 1);
            $prevButton = [
                'href' => $url . '#' . $this->pagelessCacheKey,
            ];
        }
        if ($this->page == $pageCount || $this->page > $pageCount) {
            $nextButton = [
                'disabled' => true,
            ];
        } else {
            $url = $this->_updateUrlParam('page', $this->page + 1);
            $nextButton = [
                'href' => $url . '#' . $this->pagelessCacheKey,
            ];
        }

        return [
            'prev' => $prevButton,
            'next' => $nextButton,
        ];
    }

    private function _updateUrlParam($key = '', $value = '', $parent = null): string
    {
        $url = $_SERVER['REQUEST_URI'];
        $urlParts = parse_url($url);
        if (isset($urlParts['query'])) { // Avoid 'Undefined index: query'
            parse_str($urlParts['query'], $params);
        } else {
            $params = [];
        }
        if ($parent) {
            if (!isset($params[$parent])) {
                $params[$parent] = [];
            }
            $params[$parent][$key] = $value;
        } else {
            $params[$key] = $value;
        }

        return '?' . http_build_query($params);
    }

    public function checkFilterForContent($filter, $value): bool
    {
        $options = ['countOnly' => true, 'filter' => $filter, 'values' => [$value]];
        $elements = $this->_getFeedElements($this->filters, $options);

        return $elements['total'] > 0;
    }

}

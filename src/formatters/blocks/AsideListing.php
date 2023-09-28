<?php

namespace USChamber\ComponentLibrary\formatters\blocks;

use craft\elements\Entry;
use USChamber\ComponentLibrary\base\BaseVizyFormatter;

/**
 *
 * @property-read string $componentId
 */
class AsideListing extends BaseVizyFormatter
{

    public string $articleLimitField = 'number';
    public string $mainTopicField = 'mainChamberTopic';
    public string $title = '';
    public int $articleLimit = 3;
    public array $pinnedEntries = [];

    public static function getFormatterId(): string
    {
        return 'asideListing';
    }

    public function getComponentId(): string
    {
        return '@block:aside-listing';
    }

    public function init(): void
    {
        $this->title = $this->data['values']['content']['fields']['headline'] ?? '';
        $this->pinnedEntries = $this->data['values']['content']['fields']['articles'] ?? [];
        $this->articleLimit = $this->data['values']['content']['fields'][$this->articleLimitField] ?? 3;
    }

    protected function getRelatedEntries($topic, $topicField, $queryLimit): array
    {
        return Entry::find()
            ->relatedTo([
                [
                    'targetElement' => $topic,
                    'field' => $topicField,
                ],
            ])
            ->id('and, not ' . $this->entry->id)
            ->status('live')
            ->limit($queryLimit)
            ->all();
    }

    public function getData(): array
    {
        $asideListing = [];
        $asideListing['title'] = $this->title;
        $asideListing['items'] = [];

        if ($this->pinnedEntries !== '' && count($this->pinnedEntries) > 0) {
            foreach ($this->pinnedEntries as $entry) {
                $entry = Entry::find()->id($entry)->status('live')->one();
                // if article is null or not published, skip it
                if ($entry === null) {
                    continue;
                }

                $articleObject = [
                    'href' => $entry['url'],
                    'title' => $entry['title'],
                ];
                $asideListing['items'][] = $articleObject;
            }
        }

        $entryCount = count($asideListing['items']);
        $topic = $this->entry->{$this->mainTopicField};

        if ($entryCount < $this->articleLimit && $topic !== null) {
            $relatedEntries = $this->getRelatedEntries($topic->one(), $this->mainTopicField, $this->articleLimit - $entryCount);

            foreach ($relatedEntries as $entry) {
                $articleObject = [
                    'href' => $entry['url'],
                    'title' => $entry['title'],
                ];
                $asideListing['items'][] = $articleObject;
            }
        }

        // In case author pins too many articles
        $asideListing['items'] = array_slice($asideListing['items'], 0, $this->articleLimit);

        return $asideListing;
    }
}

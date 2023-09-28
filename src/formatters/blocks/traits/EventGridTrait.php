<?php

namespace USChamber\ComponentLibrary\formatters\blocks\traits;

use craft\base\Element;
use craft\elements\Category;
use craft\elements\db\ElementQueryInterface;
use craft\elements\Entry;
use USChamber\ComponentLibrary\helpers\NavigationHelper;

trait EventGridTrait
{
    public mixed $selectedTopic = null;
    public int $limit = 4;
    protected string $topicFieldName = 'mainChamberTopic';

    public function init(): void
    {
        $selectedTopics = $this->entry?->{$this->topicFieldName};
        if ($selectedTopics !== null) {
            $this->selectedTopic = $selectedTopics->one();
        }
        $type = $this->type;
        $this->limit = ($type === 'eventGrid3') ? 3 : 4;
    }

    public function getData(): array
    {

        $events = $this->getEvents($this->selectedTopic, $this->limit);
        $eventCards = $this->createEventCards($events);

        return [
            'items' => $eventCards,
        ];
    }

    protected function createEventsQuery($topic, $limit): ElementQueryInterface
    {
        $mainQuery = Entry::find()
            ->section('events')
            ->endDate('>= now')
            ->orderBy('eventDate', 'desc');
        if ($topic) {
            $mainQuery->relatedTo([
                [
                    'targetElement' => $topic,
                    'field' => $this->topicFieldName,
                ],
            ]);
        }

        if ($limit) {
            $mainQuery->limit($limit);
        }

        return $mainQuery;
    }

    protected function createFillerQuery($limit, $mainQueryCount, $existingIds): ElementQueryInterface
    {
        $fillerQuery = Entry::find()
            ->section('events')
            ->endDate('>= now')
            ->orderBy('eventDate', 'desc')
            ->limit($limit - $mainQueryCount);
        if ($mainQueryCount > 0) {
            $fillerQuery->id('and, not ' . implode(', not ', $existingIds));
        }

        return $fillerQuery;
    }

    public function getEvents(Element $topic = null, int $limit = null, bool $backFill = true): array
    {
        // make a request to get all possible events (possibly limited by topic)
        $mainQuery = $this->createEventsQuery($topic, $limit);
        $mainQueryCount = $mainQuery->count();
        // if we haven't hit the limit, fill with the rest of the events (excluding any we have)
        if ($mainQueryCount < $limit && $backFill) {
            $fillerQuery = $this->createFillerQuery($limit, $mainQueryCount, $mainQuery->ids());
            $events = array_merge($mainQuery->all(), $fillerQuery->all());
            // order events by eventDate
            usort($events, static function($a, $b) {
                return $a->eventDate->getTimestamp() - $b->eventDate->getTimestamp();
            });
        } else {
            $events = $mainQuery->all();
        }

        return $events;
    }

    public function createEventCards($events): array
    {
        $eventCards = [];
        foreach ($events as $key => $event) {
            // separate the date into date and time
            $startDate = $event->eventDate;
            $startDateFormatted = $startDate->format('l, F d');
            $startTimeFormatted = $startDate->format('h:i A T');
            $endDate = $event->endDate;
            $endDateFormatted = $endDate->format('l, F d');
            $endTimeFormatted = $endDate->format('h:i A T');
            $date = $startDateFormatted;
            $currentDate = new \DateTime('now');
            $isLive = $currentDate > $startDate && $currentDate < $endDate;
            if ($isLive) {
                $date = 'Live Now';
            } elseif ($startDateFormatted !== $endDateFormatted) {
                $date = $startDateFormatted . ' - ' . $endDateFormatted;
            }

            $linkFieldType = $event->linkField->type;
            $liveEventHref = $event->linkField->value ?? null;

            if ($liveEventHref !== null && $isLive) {
                switch ($linkFieldType) {
                    case 'entry':
                    {
                        $href = Entry::find()
                            ->id($event->linkField->value)
                            ->status('live')
                            ->one()
                            ->url;
                        break;
                    }
                    case 'category':
                    {
                        $href = Category::find()
                            ->id($event->linkField->value)
                            ->status('enabled')
                            ->one()
                            ->url;
                        break;
                    }
                    default:
                        $href = $event->linkField->value === '' ? '#' : $event->linkField->value;
                }
            } else {
                $href = NavigationHelper::getElementUrl($event);
            }

            $eventCards[] = [
                'type' => '@card:date',
                'data' => [
                    'title' => $event->title,
                    'label' => $event->{$this->topicFieldName}->one(),
                    'date' => $date,
                    'time' => $startTimeFormatted . ' - ' . $endTimeFormatted,
                    'cta' => 'Learn More',
                    'eventId' => $event->id,
                ],
            ];
            if ($href !== '#') {
                $eventCards[$key]['data']['href'] = $href;
            }
        }

        return $eventCards;
    }
}


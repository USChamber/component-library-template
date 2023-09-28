<?php

namespace USChamber\ComponentLibrary\helpers;

use Craft;
use Twig\Markup;
use USChamber\ComponentLibrary\base\ContextEnum;
use USChamber\ComponentLibrary\ComponentLibrary;
use yii\base\ExitException;
use yii\web\NotFoundHttpException;

class VizyHelper
{
    /**
     * Transform Vizy Data to block data
     * so you can take: entry.articleBodyVizy.all()
     * and provide data in the right format for our blocks
     *
     * @throws ExitException
     * @throws NotFoundHttpException
     */
    public static function getComponentBlocks($blocks = [], $entry = null, $calledInternally = false, $options = []): array
    {
        $renderContext = ContextEnum::ARTICLE;
        if (isset($entry['landingPageVizy']) || isset($entry['landingPageBuilder'])) {
            $renderContext = ContextEnum::LANDING;
        }
        $componentBlocks = [];

        foreach ($blocks as &$data) {
            $type = $data['type'] === 'vizyBlock' ? $data['handle'] : $data['type'];
            $data = $data['type'] === 'vizyBlock' ? $data['attrs'] : $data;
            // In some migration scenarios we get plain text that doesn't get wrapped in a paragraph tag
            // This logic attempts to address that by mapping null tags with a rawNode of type text
            // to the paragraph pattern and handing off $rawData to the `transformVizyText` method below
            if ($type === 'text' && $data['tagName'] === null && $data['rawNode']['type'] === 'text') {
                $type = 'paragraph';
            }

            // initialize the data object
            $comp['data'] = [];

            // Skip this loop if disabled
            if (isset($data['enabled']) && !$data['enabled']) {
                continue;
            }
            $formatter = ComponentLibrary::getInstance()->formatters->getFormatter($type, [
                'data' => $data,
                'context' => $renderContext,
                'type' => $type,
                'entry' => $entry,
            ]);

            $comp['type'] = $formatter->getComponentId();
            $comp['data'] = $formatter->getData();

            $componentBlocks[] = $comp;
        }
        /* endfor */

        if (isset($_GET['debug']) && $_GET['debug'] === 'true' && CRAFT_ENVIRONMENT !== 'production' && $calledInternally === false) {
            Craft::dd($componentBlocks);
        }

        return $componentBlocks;
    }

    /**
     * Transform Vizy Text
     * Vizy text tends to come in arrays in arrays
     * where things like bold, italic, underline and links
     * are "marks". So this function generates a HTML safe
     * string to use in blocks
     */
    public static function transformVizyText($text = [])
    {
        $str = '';
        if (is_array($text)) {
            foreach ($text as &$item) {
                if (isset($item['marks']) && is_array($item['marks'])) {
                    $open = '';
                    $close = '';
                    foreach ($item['marks'] as &$mark) {
                        if ($mark['type'] === 'link') {
                            $target = isset($mark['attrs']['target']) ? " target=\"{$mark['attrs']['target']}\"" : '';
                            $href = $mark['attrs']['href'];
                            // vizy leaves a trailing # with entry IDs visible - remove it
                            if (str_contains($href, '#') && str_contains($href, Craft::getAlias('@web'))) {
                                $href = substr($href, 0, strpos($href, '#'));
                            }
                            $open .= "<a href=\"{$href}\"{$target}>";
                            $close .= '</a>';
                        }
                        if ($mark['type'] === 'bold') {
                            $open .= '<strong>';
                            $close .= '</strong>';
                        }
                        if ($mark['type'] === 'italic') {
                            $open .= '<em>';
                            $close .= '</em>';
                        }
                        if ($mark['type'] === 'underline') {
                            $open .= '<u>';
                            $close .= '</u>';
                        }
                        if ($mark['type'] === 'highlight') {
                            $open .= '<mark>';
                            $close .= '</mark>';
                        }
                        if ($mark['type'] === 'superscript') {
                            $open .= '<sup>';
                            $close .= '</sup>';
                        }
                    }
                    unset($mark);
                    if (isset($item['text'])) {
                        $str .= $open . $item['text'] . $close;
                    }
                } elseif (isset($item['type']) && $item['type'] === 'hardBreak') {
                    $str .= '<br>';
                } elseif (isset($item['text'])) {
                    if ($item['text'] === ' ') {
                        $str .= '​ '; // Twig looses " ", so using hidden zero width space entity
                    } else {
                        $str .= $item['text'];
                    }
                }
            }
        } else {
            return $text;
        }

        return new Markup($str, 'UTF-8');
    }

    /**
     * Returns a fully formatted list. This can be called recursively by supplying "true" as the third argument.
     */
    public static function formatList($node, $type, $isSubList = false): array
    {
        $blockType = ($type === 'bulletList') ? '@block:list' : '@block:ordered-list';
        $items = [];
        // loop through all nodes in the list
        foreach ($node['content'] as $item) {
            foreach ($item['content'] as &$listItem) {
                // formatListItem will either return an array of format ['text'=>'asfdd'] or recursively return a sublist from this function
                // check if it's text (paragraph) or not. If not, append it to the previous li
                $li = self::formatListItem($listItem, $type);
                if (is_object($li)) {
                    $items[] = $li;
                } else {
                    $prevIndex = count($items) - 1;
                    if ($prevIndex < 0) {
                        return $items;
                    }
                    $prevItem = $items[$prevIndex];
                    $items[$prevIndex] = [
                        $prevItem,
                        $li,
                    ];
                }
            }
        }
        // if a sublist, return the proper structure to tell the fractal
        if ($isSubList) {
            $subList = [];
            $subList['type'] = $blockType;
            $subList['data'] = [];
            $subList['data']['items'] = $items;

            return $subList;
        } else {
            // otherwise just return the naked array.
            return $items;
        }
    }

    /**
     * Returns a fully formatted list item. Either as a standard string or as an
     * object of nested lists
     */
    public static function formatListItem($listItem, $parentType)
    {
        if (isset($listItem['content'])) {
            // if text, will see paragraph type
            if ($listItem['type'] === 'paragraph') {
                return self::transformVizyText($listItem['content']);
                // if sublist->bullets, will see bulletList type
            }

            if ($listItem['type'] === 'bulletList') {
                return self::formatList($listItem, $parentType, true);
                // if sublist->numbers, will see orderedList type
            }

            if ($listItem['type'] === 'orderedList') {
                return self::formatList($listItem, $parentType, true);
            }

            return '';
        }
    }
}

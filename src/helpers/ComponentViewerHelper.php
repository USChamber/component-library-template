<?php

namespace USChamber\ComponentLibrary\helpers;

use cebe\markdown\Markdown;
use Craft;
use craft\helpers\Json;
use USChamber\ComponentLibrary\ComponentLibrary;
use yii\base\Exception;

class ComponentViewerHelper
{
    public static function getComponentConfigPath($componentLocation): string
    {
        return str_replace('.twig', '.config.json', $componentLocation);
    }

    public static function getComponentReadmePath($componentId, $componentLocation): string
    {
        $componentName = explode(':', $componentId);
        $componentName = $componentName[count($componentName) - 1];
        $readmeFilePath = str_replace($componentName . '.twig', 'readme.md', $componentLocation);
        // if the readmeFilePath contains readme.md
        if (str_contains($readmeFilePath, 'readme.md')) {
            return CRAFT_BASE_PATH . '/templates/' . $readmeFilePath;
        }
        $readmeFilePath = str_replace('input.twig', 'readme.md', $componentLocation);

        return CRAFT_BASE_PATH . '/' . $readmeFilePath;
    }

    /**
     * @throws Exception
     */
    public static function getComponentContext($componentId, $variant): array
    {
        $componentMap = ComponentLibrary::getInstance()->formatters->getComponentMap();

        // load in the components-map.json file as an array
        $componentConfigPath = $componentMap[$componentId];
        // swap out the '.twig' extension for '.config.json'
        $componentConfig = Json::decode(file_get_contents(self::getComponentConfigPath($componentConfigPath)));

        $context = $componentConfig['context'] ?? [];
        $variants = $componentConfig['variants'] ?? [];

        if (isset($variants[(int)$variant])) {
            // merge context with variant context, variant should always override if matching props
            return array_merge($context, $variants[(int)$variant]['context']);
        }

        if ($context) {
            return $context;
        }

        return $variants[0]['context'] ?? [];
    }

    /**
     * @throws Exception
     */
    public static function getAllComponents(): array
    {
        $componentMap = ComponentLibrary::getInstance()->formatters->getComponentMap();
        $componentConfig = [];
        $errors = [];

        foreach ($componentMap as $componentId => $componentPath) {
            try {
                $config = Json::decode(file_get_contents(self::getComponentConfigPath($componentPath)));
                $componentParts = explode(':', $componentId);

                // remove any '@' value from $exploded and capitalize the first letter of the string
                [$componentGroup, $componentName] = $componentParts;
                $componentGroup = ucfirst(str_replace('@', '', $componentGroup));
                $componentName = $componentName ?? '';

                $componentConfig[$componentGroup]['components'][$componentName] = $config;
            } catch (\Exception $e) {
                $errors[$componentId] = [
                    'error' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'line' => $e->getLine(),
                    ],
                    'path' => self::getComponentConfigPath($componentPath),
                ];
            }
        }

        // Adds a 'buttons' array to each component that normalizes variant and non-variant data into
        // a single array for our sidebar behavior
        foreach ($componentConfig as $componentGroup => $componentGroupConfig) {
            foreach ($componentGroupConfig['components'] as $componentName => $config) {

                $buttons = [];
                if (!empty($config['variants'])) {
                    foreach ($config['variants'] as $variant) {
                        $buttons[] = [
                            'label' => $variant['name'],
                        ];
                    }
                } else {
                    $buttons[] = [
                        'label' => $componentName,
                    ];
                }

                // Odd naming convention here but having buttonVariants->buttons made it easier on template logic for now
                $componentConfig[$componentGroup]['components'][$componentName]['buttonVariants']['buttons'] = [
                    'componentId' => $config['handle'],
                    'buttons' => $buttons,
                ];
            }
        }

        $componentConfig['Errors'] = $errors;

        return $componentConfig;
    }

    /**
     * @throws Exception
     */
    public static function getComponent($componentId = '', $variant = null): array
    {
        $componentMap = ComponentLibrary::getInstance()->formatters->getComponentMap();

        // load in the components-map.json file as an array
        $componentConfigPath = $componentMap[$componentId];
        $twigString = file_get_contents($componentConfigPath);
        $componentConfig = Json::decode(file_get_contents(self::getComponentConfigPath($componentConfigPath)));
        $context = self::getComponentContext($componentId, $variant);

        // Override the default context with the variant context
        if ($context) {
            $componentConfig['context'] = $context;
        }

        try {
            $rendered = Craft::$app->view->renderString($twigString, $context);
        } catch (\Exception $e) {
            $rendered = $e->getMessage();
        }

        try {
            $readme = file_get_contents(self::getComponentReadmePath($componentId, $componentConfigPath));
        } catch (\Exception) {
            $readme = 'Tags: {{ tags|join(",") }}<br /><h2> {{ title }}: {{ handle }}</h2><pre><code class="language-twig">&#123;&#37; include \'@{{ handle }}\'';
            
            // Only show `with { … }` in the include example if context exists
            if ($context) {
                $readme = $readme . ' with {{ context|json_encode(constant(\'JSON_PRETTY_PRINT\')) }}';
            }

            $readme = $readme . ' &#37;&#125;</code></pre>';
        }
        try {
            $parser = new Markdown();
            $readme = $parser->parse($readme);
            $readme = Craft::$app->view->renderString($readme, $componentConfig);
        } catch (\Exception $e) {
            $readme = $e->getMessage();
        }

        return [
            'config' => $componentConfig,
            'context' => $context,
            'readme' => $readme,
            'twig' => $twigString,
            'rendered' => $rendered,
            'filepath' => $componentConfigPath,
        ];
    }

    public static function getGitHistory($componentId): array
    {
        $componentMap = ComponentLibrary::getInstance()->formatters->getComponentMap();

        try {
            $filePath = CRAFT_BASE_PATH . '/' . $componentMap[$componentId];
            $output = [];
            $command = 'git log --follow --pretty=format:"%H|%an|%ad|%s" --date=short --name-only ' . $filePath;
            exec($command, $output);
            $refinedOutput = [];
            $currentIndex = 0;
            foreach ($output as $key => $value) {
                // json decode every third line starting with the first
                if ($key % 3 === 0) {
                    $exploded = explode('|', $value);
                    $refinedOutput[$currentIndex] = [
                        'commit' => $exploded[0],
                        'author' => $exploded[1],
                        'date' => $exploded[2],
                        'message' => $exploded[3],
                    ];
                }
                // append every third line starting with the second to the previous line
                if ($key % 3 === 1) {
                    $refinedOutput[$currentIndex]['filepath'] = $value;
                    $currentIndex++;
                }
            }

            return [
                'history' => $refinedOutput,
                'raw' => $output,
                'command' => $command,
                'status' => 'success',
            ];
        } catch (\Exception $e) {
            return [
                'command' => $command,
                'raw' => $output ?? [],
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}

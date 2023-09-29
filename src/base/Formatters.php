<?php

namespace USChamber\ComponentLibrary\base;

use Craft;
use craft\errors\DeprecationException;
use craft\errors\MissingComponentException;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\Json;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use USChamber\ComponentLibrary\formatters\blocks\UnknownBlock;
use yii\base\Component;
use yii\base\Exception;
use yii\web\NotFoundHttpException;

/**
 *
 * @property-read array $componentMap
 * @property-read array $formatters
 */
class Formatters extends Component
{
    public const EVENT_REGISTER_FORMATTERS = 'registerFormatters';

    /**
     * Register Formatters using EVENT_REGISTER_FORMATTERS
     *
     * [
     *   '@componentId' => [
     *     'formatterId' => CustomFormatter::class,
     *   ]
     *   '@global:header' => [
     *     'top-nav' => TopNav::class,
     *   ],
     * ]
     */
    private array $_formatters = [];
    private ?array $_componentMap = null;

    public function getFormatters(): array
    {
        if (!$this->_formatters) {
            $this->initFormatters();
        }

        return $this->_formatters;
    }

    public function initFormatters(): void
    {
        $event = new RegisterComponentTypesEvent([
            'types' => [],
        ]);

        $this->trigger(self::EVENT_REGISTER_FORMATTERS, $event);

        /** @var FormatterInterface $formatter */
        foreach ($event->types as $formatter) {
            $this->_formatters[$formatter::getFormatterId()] = $formatter;
        }

        $pluginConfig = Craft::$app->getConfig()->getConfigFromFile('component-library');
        $formatterOverrides = $pluginConfig['formatterOverrides'] ?? [];
        foreach ($formatterOverrides as $formatterOverride) {
            $this->_formatters[$formatterOverride::getFormatterId()] = $formatterOverride;
        }
    }

    /**
     * @throws DeprecationException
     * @throws NotFoundHttpException
     */
    public function getFormatter(string $formatterId, $config = []): BaseFormatter
    {
        $formatters = $this->getFormatters();

        if (!$formatterClass = $formatters[$formatterId] ?? null) {
            Craft::$app->getDeprecator()->log('Unknown Block', 'Formatter ID: ' . $formatterId . 'Config: ' . Json::encode($config));

            return new UnknownBlock($config);
        }

        $formatter = new $formatterClass($config);

        if (!$formatter instanceof BaseFormatter) {
            throw new NotFoundHttpException('Formatter is not an instance of BaseFormatter: ' . $formatterId);
        }

        return $formatter;
    }

    /**
     * @throws MissingComponentException
     */
    public function getComponentMap(): array
    {
        if ($this->_componentMap) {
            return $this->_componentMap;
        }

        $pluginConfig = Craft::$app->getConfig()->getConfigFromFile('component-library');
        $componentBaseDirs = $pluginConfig['templateDirectories'] ?? [];

        $mapping = [];

        foreach ($componentBaseDirs as $baseDir) {
            if (!is_dir($baseDir)) {
                continue;
            }

            $directoryIterator = new RecursiveDirectoryIterator($baseDir);
            foreach (new RecursiveIteratorIterator($directoryIterator) as $filename => $file) {
                if ($file->getExtension() === 'json') {
                    $json = file_get_contents($filename);
                    $component = Json::decode($json, true);
                    $twigFile = str_replace('config.json', 'twig', $filename);

                    if (isset($component['handle'])) {
                        $componentName = '@' . $component['handle'];
                        $mapping[$componentName] = $twigFile;
                    } else {
                        throw new MissingComponentException('Missing handle for component: ' . $filename);
                    }
                }
            }
        }

        $this->_componentMap = $mapping;

        return $this->_componentMap;
    }

    /**
     * @throws NotFoundHttpException|DeprecationException
     */
    public function getData(string $formatterId, $data = []): array
    {
        $formatter = $this->getFormatter($formatterId, [
            'data' => $data,
        ]);

        return $formatter->getData();
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws Exception
     * @throws LoaderError
     * @throws NotFoundHttpException
     */
    public function getHtml(string $formatterId, $data = null): ?Markup
    {
        $formatter = $this->getFormatter($formatterId, [
            'data' => $data,
        ]);

        return $formatter->getHtml();
    }
}

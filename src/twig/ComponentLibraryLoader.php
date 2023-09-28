<?php

namespace USChamber\ComponentLibrary\twig;

use Craft;
use craft\web\twig\TemplateLoader;
use craft\web\twig\TemplateLoaderException;
use craft\web\View;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;
use USChamber\ComponentLibrary\ComponentLibrary;
use yii\base\Exception;
use yii\base\ExitException;

/**
 * @see TemplateLoader
 */
class ComponentLibraryLoader implements LoaderInterface
{
    protected ?View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function exists($name): bool
    {
        return $this->view->doesTemplateExist($name);
    }

    /**
     * @inheritDoc
     */
    public function getSourceContext($name): Source
    {
        $template = $this->findTemplate($name);

        if (!is_readable($template)) {
            throw new LoaderError('Template not found: ' . $name);
        }

        return new Source(file_get_contents($template), $name, $template);
    }

    /**
     * @inheritDoc
     */
    public function getCacheKey($name): string
    {
        try {
            return $this->findTemplate($name);
        } catch (\Exception $e) {
            return 'Template Not Found: ' . $e->getMessage();
        }
    }

    /**
     * @throws TemplateLoaderException
     * @throws Exception
     * @throws LoaderError|ExitException
     */
    protected function findTemplate(string $name): string
    {
        // Fractal component items begin with an `@` symbol
        if (str_starts_with($name, '@')) {
            $componentMap = ComponentLibrary::getInstance()->formatters->getComponentMap();
            $template = $componentMap[$name] ?? null;

            if (!$template) {
                throw new Exception(Craft::t('app', 'Could not find component: {componentId}', [
                    'componentId' => $name,
                ]));
            }
        } else {
            $template = $this->view->resolveTemplate($name);
        }

        if (!$template) {
            throw new TemplateLoaderException($name, Craft::t('app', 'Unable to find the template “{template}”.', ['template' => $name]));
        }

        return $template;
    }

    /**
     * @throws TemplateLoaderException
     * @throws Exception
     * @throws LoaderError
     */
    public function isFresh($name, $time): bool
    {
        // If this is a control panel request and a DB update is needed, force a recompile.
        $request = Craft::$app->getRequest();

        if (version_compare(Craft::$app->getInfo()->version, '4', '<')) {
            $isUpdatePending = Craft::$app->getUpdates()->getIsCraftDbMigrationNeeded();
        } else {
            $isUpdatePending = Craft::$app->getUpdates()->getIsCraftUpdatePending();
        }

        if ($request->getIsCpRequest() && $isUpdatePending) {
            return false;
        }

        $sourceModifiedTime = filemtime($this->findTemplate($name));

        return $sourceModifiedTime <= $time;
    }

    /**
     * Returns the path to a given template, or throws a TemplateLoaderException.
     *
     * @param string $name
     *
     * @return string
     * @throws TemplateLoaderException|LoaderError if the template doesn't exist
     */
    private function _resolveTemplate(string $name): string
    {
        $template = $this->view->resolveTemplate($name);

        if ($template !== false) {
            return $template;
        }

        throw new TemplateLoaderException($name, Craft::t('app', 'Unable to find the template “{template}”.', ['template' => $name]));
    }
}

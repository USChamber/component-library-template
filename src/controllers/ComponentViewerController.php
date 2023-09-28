<?php

namespace USChamber\ComponentLibrary\controllers;

use Craft;
use craft\web\Controller;
use USChamber\ComponentLibrary\helpers\ComponentViewerHelper;
use yii\web\Response;

class ComponentViewerController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * @var string[]
     */
    protected int|bool|array $allowAnonymous = ['get-component-info', 'get-component-history'];

    // Public Methods
    // =========================================================================

    public function actionIndex(): Response
    {
        return $this->asJson(['success' => true]);
    }

    public function actionGetComponentInfo(): Response
    {
        // get componentId from request params
        $componentId = Craft::$app->request->getParam('componentId');
        $variant = Craft::$app->request->getParam('variant');
        $component = ComponentViewerHelper::getComponent($componentId, $variant);

        return $this->asJson($component);
    }

    public function actionGetComponentHistory(): Response
    {
        // get componentId from request params
        $componentId = Craft::$app->request->getParam('componentId');
        $history = ComponentViewerHelper::getGitHistory($componentId);

        return $this->asJson($history);
    }
}

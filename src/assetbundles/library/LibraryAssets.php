<?php

namespace USChamber\ComponentLibrary\assetbundles\library;

use craft\web\AssetBundle;

class LibraryAssets extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init(): void
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@USChamber/ComponentLibrary/assetbundles/library/dist';

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/app.js',
            'js/highlight.min.js',
        ];

        $this->css = [
            'css/app.css',
        ];

        parent::init();
    }
}

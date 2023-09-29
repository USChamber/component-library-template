<?php

namespace USChamber\ComponentLibrary;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\helpers\Json;
use craft\web\View;
use USChamber\ComponentLibrary\base\Formatters;
use USChamber\ComponentLibrary\formatters\blocks\AccordionBlock;
use USChamber\ComponentLibrary\formatters\blocks\Arc;
use USChamber\ComponentLibrary\formatters\blocks\AsideListing;
use USChamber\ComponentLibrary\formatters\blocks\AutoAside;
use USChamber\ComponentLibrary\formatters\blocks\Blockquote;
use USChamber\ComponentLibrary\formatters\blocks\BulletList;
use USChamber\ComponentLibrary\formatters\blocks\CenterColumn;
use USChamber\ComponentLibrary\formatters\blocks\ColSet7525;
use USChamber\ComponentLibrary\formatters\blocks\ColSet7525Aside;
use USChamber\ComponentLibrary\formatters\blocks\Counter;
use USChamber\ComponentLibrary\formatters\blocks\DataTable;
use USChamber\ComponentLibrary\formatters\blocks\EditorialList;
use USChamber\ComponentLibrary\formatters\blocks\EventGrid;
use USChamber\ComponentLibrary\formatters\blocks\EventGrid3;
use USChamber\ComponentLibrary\formatters\blocks\EventGrid4;
use USChamber\ComponentLibrary\formatters\blocks\Feature3Category;
use USChamber\ComponentLibrary\formatters\blocks\Feature3VideoCategory;
use USChamber\ComponentLibrary\formatters\blocks\Feature4Category;
use USChamber\ComponentLibrary\formatters\blocks\Feature4VideoCategory;
use USChamber\ComponentLibrary\formatters\blocks\FeatureExpressive;
use USChamber\ComponentLibrary\formatters\blocks\FeatureExpressiveVideo;
use USChamber\ComponentLibrary\formatters\blocks\FeatureFiftyFifty;
use USChamber\ComponentLibrary\formatters\blocks\FeatureGrid;
use USChamber\ComponentLibrary\formatters\blocks\FeatureGrid3;
use USChamber\ComponentLibrary\formatters\blocks\FeatureGrid3ContentType;
use USChamber\ComponentLibrary\formatters\blocks\FeatureGrid3CustomCards;
use USChamber\ComponentLibrary\formatters\blocks\FeatureGrid3LinksCategory;
use USChamber\ComponentLibrary\formatters\blocks\FeatureGrid3PersonType;
use USChamber\ComponentLibrary\formatters\blocks\FeatureGrid4;
use USChamber\ComponentLibrary\formatters\blocks\FeatureGrid4ContentType;
use USChamber\ComponentLibrary\formatters\blocks\FeatureGrid4CustomCards;
use USChamber\ComponentLibrary\formatters\blocks\FeatureGrid4LinksCategory;
use USChamber\ComponentLibrary\formatters\blocks\FeatureGrid4PersonType;
use USChamber\ComponentLibrary\formatters\blocks\FeatureGridContentType;
use USChamber\ComponentLibrary\formatters\blocks\FeatureGridCustomCards;
use USChamber\ComponentLibrary\formatters\blocks\FeatureGridPersonType;
use USChamber\ComponentLibrary\formatters\blocks\FeatureListing;
use USChamber\ComponentLibrary\formatters\blocks\FeatureListingsB;
use USChamber\ComponentLibrary\formatters\blocks\FeatureListingsD;
use USChamber\ComponentLibrary\formatters\blocks\FeatureListingsE;
use USChamber\ComponentLibrary\formatters\blocks\Form5050;
use USChamber\ComponentLibrary\formatters\blocks\FormEmbedInPage;
use USChamber\ComponentLibrary\formatters\blocks\FormModal;
use USChamber\ComponentLibrary\formatters\blocks\FullWidthStat;
use USChamber\ComponentLibrary\formatters\blocks\HardBreak;
use USChamber\ComponentLibrary\formatters\blocks\Heading;
use USChamber\ComponentLibrary\formatters\blocks\HorizontalRule;
use USChamber\ComponentLibrary\formatters\blocks\Image;
use USChamber\ComponentLibrary\formatters\blocks\ImageFullBleed;
use USChamber\ComponentLibrary\formatters\blocks\ImageGallery;
use USChamber\ComponentLibrary\formatters\blocks\ImageRepeater;
use USChamber\ComponentLibrary\formatters\blocks\ImageWithCaption;
use USChamber\ComponentLibrary\formatters\blocks\InlineFeature;
use USChamber\ComponentLibrary\formatters\blocks\InlineFeatures;
use USChamber\ComponentLibrary\formatters\blocks\MapEmbed;
use USChamber\ComponentLibrary\formatters\blocks\OrderedList;
use USChamber\ComponentLibrary\formatters\blocks\OurWork;
use USChamber\ComponentLibrary\formatters\blocks\Paragraph;
use USChamber\ComponentLibrary\formatters\blocks\PromoCtaBlock;
use USChamber\ComponentLibrary\formatters\blocks\PromoCtaFormEmbed;
use USChamber\ComponentLibrary\formatters\blocks\Pullquote;
use USChamber\ComponentLibrary\formatters\blocks\SimpleLinks;
use USChamber\ComponentLibrary\formatters\blocks\Stat;
use USChamber\ComponentLibrary\formatters\blocks\TableOfContents;
use USChamber\ComponentLibrary\formatters\blocks\Testimonial;
use USChamber\ComponentLibrary\formatters\blocks\TitleBar;
use USChamber\ComponentLibrary\formatters\blocks\UnknownBlock;
use USChamber\ComponentLibrary\formatters\blocks\Video;
use USChamber\ComponentLibrary\formatters\blocks\VideoFullBleed;
use USChamber\ComponentLibrary\formatters\blocks\VideoWide;
use USChamber\ComponentLibrary\formatters\globals\CaseIssuesFooter;
use USChamber\ComponentLibrary\formatters\globals\FooterNav;
use USChamber\ComponentLibrary\formatters\globals\TopNav;
use USChamber\ComponentLibrary\twig\ComponentLibraryLoader;
use USChamber\ComponentLibrary\twig\ComponentLibraryTwigExtension;
use yii\base\Event;
use yii\base\Module;

/**
 * @property-read string[] $formatterTypes
 * @property Formatters $formatters
 */
class ComponentLibrary extends Module
{
    public function init(): void
    {
        parent::init();

        $this->setComponents([
            'formatters' => Formatters::class,
        ]);

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'USChamber\ComponentLibrary\console\controllers';
        } else {
            $this->controllerNamespace = 'USChamber\ComponentLibrary\controllers';
        }

        Craft::setAlias('@USChamber/ComponentLibrary', $this->getBasePath());

        $view = Craft::$app->view;
        $view->getTwig()->setLoader(new ComponentLibraryLoader($view));
        $view->registerTwigExtension(new ComponentLibraryTwigExtension());

        Event::on(
            Formatters::class,
            Formatters::EVENT_REGISTER_FORMATTERS,
            function(RegisterComponentTypesEvent $event) {
                $event->types = $this->getFormatterTypes();
            });

        Event::on(View::class, View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            if (is_dir($baseDir = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates')) {
                $e->roots[$this->id] = $baseDir;
            }
        });
    }

    public function getFormatterTypes(): array
    {
        return [
            // Globals
            TopNav::class,
            FooterNav::class,

            // Blocks
            AccordionBlock::class,
            AsideListing::class,
            Blockquote::class,
            BulletList::class,
            CenterColumn::class,
            EventGrid::class,
            Heading::class,
            Image::class,
            Paragraph::class,
            Stat::class,
            UnknownBlock::class,
            Video::class,
        ];
    }
}

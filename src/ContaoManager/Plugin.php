<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-formvalidation-bundle
 */

namespace Trilobit\FormvalidationBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

use Trilobit\FormvalidationBundle\TrilobitFormvalidationBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CommentsBundle\ContaoCommentsBundle;
use Contao\NewsletterBundle\ContaoNewsletterBundle;

/**
 * Plugin for the Contao Manager.
 */
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(TrilobitFormvalidationBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class, ContaoCommentsBundle::class, ContaoNewsletterBundle::class]),
        ];
    }
}

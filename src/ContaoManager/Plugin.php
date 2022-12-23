<?php

declare(strict_types=1);

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 */

namespace Trilobit\FormvalidationBundle\ContaoManager;

use Contao\CommentsBundle\ContaoCommentsBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\NewsletterBundle\ContaoNewsletterBundle;
use Trilobit\FormvalidationBundle\TrilobitFormvalidationBundle;

/**
 * Plugin for the Contao Manager.
 */
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritDoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(TrilobitFormvalidationBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    ContaoCommentsBundle::class,
                    ContaoNewsletterBundle::class,
                    'notification_center',
                ]),
        ];
    }
}

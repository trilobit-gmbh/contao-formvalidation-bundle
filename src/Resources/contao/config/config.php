<?php

declare(strict_types=1);

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 */

use Trilobit\FormvalidationBundle\ContentComments;
use Trilobit\FormvalidationBundle\ModuleChangePassword;
use Trilobit\FormvalidationBundle\ModuleCloseAccount;
use Trilobit\FormvalidationBundle\ModuleComments;
use Trilobit\FormvalidationBundle\ModuleFormGenerator;
use Trilobit\FormvalidationBundle\ModuleLogin;
use Trilobit\FormvalidationBundle\ModuleNewsletterSubscribe;
use Trilobit\FormvalidationBundle\ModuleNewsletterUnsubscribe;
use Trilobit\FormvalidationBundle\ModulePersonalData;
use Trilobit\FormvalidationBundle\ModuleRegistration;

$GLOBALS['TL_CTE']['includes']['form'] = ModuleFormGenerator::class;

$GLOBALS['FE_MOD']['application']['form'] = ModuleFormGenerator::class;

$GLOBALS['FE_MOD']['user']['registration'] = ModuleRegistration::class;
$GLOBALS['FE_MOD']['user']['login'] = ModuleLogin::class;
$GLOBALS['FE_MOD']['user']['changePassword'] = ModuleChangePassword::class;
$GLOBALS['FE_MOD']['user']['lostPassword'] = class_exists(\Contao\ModulePassword::class) ? \Trilobit\FormvalidationBundle\ModulePassword::class : \Trilobit\FormvalidationBundle\ModuleLostPassword::class;
$GLOBALS['FE_MOD']['user']['closeAccount'] = ModuleCloseAccount::class;
$GLOBALS['FE_MOD']['user']['personalData'] = ModulePersonalData::class;

if (class_exists(\Contao\ModulePasswordNotificationCenter::class)) {
    $GLOBALS['FE_MOD']['user']['lostPasswordNotificationCenter'] = \Trilobit\FormvalidationBundle\ModulePasswordNotificationCenter::class;
}

if (class_exists(\Contao\ContentComments::class)) {
    $GLOBALS['TL_CTE']['includes']['comments'] = ContentComments::class;
}

if (class_exists(\Contao\ModuleComments::class)) {
    $GLOBALS['FE_MOD']['application']['comments'] = ModuleComments::class;
}

if (class_exists(\Contao\ModuleSubscribe::class)) {
    $GLOBALS['FE_MOD']['newsletter']['subscribe'] = ModuleNewsletterSubscribe::class;
}

if (class_exists(\Contao\ModuleUnsubscribe::class)) {
    $GLOBALS['FE_MOD']['newsletter']['unsubscribe'] = ModuleNewsletterUnsubscribe::class;
}

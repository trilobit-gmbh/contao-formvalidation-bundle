<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-formvalidation-bundle
 */

/**
 * System configuration.
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = str_replace(
    ';{proxy_legend',
    ';{livevalidation_legend:hide},livevalidationDisableDefaultCss;{proxy_legend',
    $GLOBALS['TL_DCA']['tl_settings']['palettes']['default']
);

// Fields
$GLOBALS['TL_DCA']['tl_settings']['fields']['livevalidationDisableDefaultCss'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['livevalidationDisableDefaultCss'],
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr w50'],
];

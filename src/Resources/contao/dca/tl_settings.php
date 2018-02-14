<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * System configuration
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = str_replace
(
    ';{proxy_legend',
    ';{livevalidation_legend:hide},livevalidationDisableDefaultCss;{proxy_legend',
    $GLOBALS['TL_DCA']['tl_settings']['palettes']['default']
);

// Fields
$GLOBALS['TL_DCA']['tl_settings']['fields']['livevalidationDisableDefaultCss'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['livevalidationDisableDefaultCss'],
    'inputType' => 'checkbox',
    'eval'      => array('tl_class'=> 'clr w50',)
);

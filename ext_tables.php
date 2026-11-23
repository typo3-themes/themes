<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

// Add some backend stylesheets and javascript
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][]
    = \KayStrobach\Themes\Hooks\PageRenderer::class . '->addJSCSS';

// TYPO3 v12 and later: the icons are registered in Configuration/Icons.php

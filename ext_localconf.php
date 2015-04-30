<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

	// Define TypoScript as content rendering template
$GLOBALS['TYPO3_CONF_VARS']['FE']['contentRenderingTemplates'] = array(
	'contentelements/Configuration/TypoScript/Static/'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'PatrickBroens.' . $_EXTKEY,
	'Contentelements',
	array(
		'ContentElement' => 'render'
	),
	array()
);

	// Remove obsolete fields and set some defaults
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
	'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:contentelements/Configuration/TypoScript/PageTSconfig/default.ts">'
);

	// Minimum set of fields, recommended for beginners, when showMinimumSetOfFields is set to 1 in Extension Manager
$extensionConfiguration = unserialize($_EXTCONF);
if(is_array($extensionConfiguration)) {
	if ($extensionConfiguration['showMinimumSetOfFields']) {
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
			'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:contentelements/Configuration/TypoScript/PageTSconfig/minimum.ts">'
		);
	}
}


// Register for hook to show preview of tt_content element of CType="textmedia" in page module
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['textmedia'] =
	\PatrickBroens\Contentelements\Hooks\PageLayoutView\TextMediaPreviewRenderer::class;

// Register for hook to render content element "bullets"
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['frontend']['contentelements']['bullets'] =
	\PatrickBroens\Contentelements\Hooks\ContentElementRenderer\BulletsRenderer::class;

// Register for hook to render content element "menu"
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['frontend']['contentelements']['menu'] =
	\PatrickBroens\Contentelements\Hooks\ContentElementRenderer\MenuRenderer::class;

// Register for hook to render content element "table"
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['frontend']['contentelements']['table'] =
	\PatrickBroens\Contentelements\Hooks\ContentElementRenderer\TableRenderer::class;

// Register for hook to render content element "textmedia"
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['frontend']['contentelements']['textmedia'] =
	\PatrickBroens\Contentelements\Hooks\ContentElementRenderer\TextMediaRenderer::class;

// Register for hook to render content element "uploads"
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['frontend']['contentelements']['uploads'] =
	\PatrickBroens\Contentelements\Hooks\ContentElementRenderer\UploadsRenderer::class;

	// Override PageRepository
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\Page\\PageRepository'] = array(
	'className' => 'PatrickBroens\\Contentelements\\Page\\PageRepository'
);
	// Override FluidTemplateContentObject
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\ContentObject\\FluidTemplateContentObject'] = array(
	'className' => 'PatrickBroens\\Contentelements\\ContentObject\\FluidTemplateContentObject'
);

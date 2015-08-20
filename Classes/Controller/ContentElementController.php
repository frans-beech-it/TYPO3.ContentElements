<?php
namespace PatrickBroens\Contentelements\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use PatrickBroens\Contentelements\Hooks\ContentElementRenderer\ContentElementRendererHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use PatrickBroens\Contentelements\View\StandaloneView;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class ContentElementController {

	/**
	 * The content object
	 *
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $contentObject;

	/**
	 * The object data
	 *
	 * @var mixed
	 */
	protected $data;

	/**
	 * The object manager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	public $objectManager;

	/**
	 * The TypoScript Frontend Controller
	 *
	 * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	protected $typoScriptFrontendController;

	/**
	 * The extension configuration
	 *
	 * @var array
	 */
	protected $configuration;

	/**
	 * The settings
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * The view
	 *
	 * @var \PatrickBroens\Contentelements\View\StandaloneView
	 */
	protected $view;

	/**
	 * Initialization of all actions
	 *
	 * @param array $controllerConfiguration The configuration for this controller
	 * @return void
	 */
	protected function initializeAction($controllerConfiguration) {
		$this->contentObject = $this->cObj;
		$this->data = $this->contentObject->data;
		$this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$this->typoScriptFrontendController = $GLOBALS['TSFE'];

		$this->initializeConfiguration($controllerConfiguration);
		$this->initializeView();
	}

	/**
	 * Initialize the configuration
	 *
	 * Get the related TypoScript
	 *
	 * @param array $controllerConfiguration The configuration for this controller
	 * @return void
	 */
	protected function initializeConfiguration($controllerConfiguration) {
		$configurationManager = $this->objectManager->get(FrontendConfigurationManager::class);
		$configurationManager->setContentObject($this->contentObject);
		$this->configuration = $configurationManager->getConfiguration('contentelements');
		$this->configuration['controllerConfiguration'] = $controllerConfiguration;
		$this->settings = $this->configuration['settings'];
	}

	/**
	 * Initialize the view
	 *
	 * @return void
	 */
	protected function initializeView() {
		$this->view = $this->objectManager->get(StandaloneView::class, $this->contentObject);
		$this->view->setTemplateRootPaths($this->getRootPaths('templateRootPaths'));
		$this->view->setLayoutRootPaths($this->getRootPaths('layoutRootPaths'));
		$this->view->setPartialRootPaths($this->getRootPaths('partialRootPaths'));

		$this->view->setTemplateName($this->configuration['controllerConfiguration']['templateName'] ?: 'Default');
	}

	/**
	 * Default action, forward on field "CType"
	 *
	 * @param string $content Extra content, in this case empty
	 * @param array $settings The settings for this action
	 * @return string
	 * @throws \UnexpectedValueException
	 */
	public function renderAction($content, array $controllerConfiguration) {
		$this->initializeAction($controllerConfiguration);

		if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['frontend']['contentelements'][$this->data['CType']])) {
			$className = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['frontend']['contentelements'][$this->data['CType']];
			$hookObject = GeneralUtility::getUserObj($className);

			if (!$hookObject instanceof ContentElementRendererHookInterface) {
				throw new \UnexpectedValueException(
					'$hookObject must implement interface ' .
					ContentElementRendererHookInterface::class,
					1427455377
				);
			}

			$hookObject->render($this->data, $this->configuration, $this->settings, $this->view, $this);
		}

		$this->view->assign('settings', $this->settings);
		$this->view->assign('data', $this->data);
		$this->view->assign('parentRecordNumber', $this->contentObject->parentRecordNumber);

		return $this->view->render();
	}

	/**
	 * Get root paths (with absolute paths)
	 *
	 * @param $name
	 * @return array
	 */
	protected function getRootPaths($name) {
		$paths = $this->getViewProperty($name);
		foreach ((array)$paths as $key => $path) {
			$paths[$key] = GeneralUtility::getFileAbsFileName($path);
		}
		return $paths;
	}

	/**
	 * Handles the path resolving for *rootPath(s)
	 *
	 * numerical arrays get ordered by key ascending
	 *
	 * @param string $setting parameter name from TypoScript
	 * @return array
	 */
	protected function getViewProperty($setting) {
		$values = array();
		if (
			!empty($this->configuration['view'][$setting])
			&& is_array($this->configuration['view'][$setting])
		) {
			$values = $this->configuration['view'][$setting];
		}

		return $values;
	}
}
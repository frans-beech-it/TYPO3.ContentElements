<?php
namespace PatrickBroens\Contentelements\ViewHelpers;

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

/**
 * The abstract base class for all frontend view helpers.
 */
abstract class AbstractFrontendViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * The TypoScript Frontend Controller
	 *
	 * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	protected $typoScriptFrontendController;

	/**
	 * The content object renderer
	 *
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $contentObject;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->typoScriptFrontendController = $GLOBALS['TSFE'];
		$this->contentObject = $this->typoScriptFrontendController->cObj;
	}
}
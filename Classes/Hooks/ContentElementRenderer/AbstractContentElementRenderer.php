<?php
namespace PatrickBroens\Contentelements\Hooks\ContentElementRenderer;

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
 * Abstract class for rendering content elements
 */
abstract class AbstractContentElementRenderer implements ContentElementRendererHookInterface {

	/**
	 * The TypoScript Frontend Controller
	 *
	 * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	protected $typoScriptFrontendController;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->typoScriptFrontendController = $GLOBALS['TSFE'];
	}
}
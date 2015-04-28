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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use PatrickBroens\Contentelements\Controller\ContentElementController;

/**
 * Class for rendering the content element "menu"
 */
class MenuRenderer extends AbstractContentElementRenderer {

	/**
	 * Render the content element "menu"
	 *
	 * The fields "pages" and "selected_categories" contain the selected pages/categories, stored as comma separated values.
	 * Before sending it to the view they are transformed to an array of integers, instead of a string.
	 * The values are filtered on removing zero and duplicates.
	 *
	 * @param array $data The data of the content element (row in tt_content table)
	 * @param array $configuration The extension configuration
	 * @param array $settings The view settings
	 * @param \TYPO3\CMS\Fluid\View\StandaloneView $view The view
	 * @param \PatrickBroens\Contentelements\Controller\ContentElementController $controller The content element controller
	 * @return void
	 */
	public function render(
		array &$data,
		array &$configuration,
		array &$settings,
		StandaloneView $view,
		ContentElementController $controller
	) {
		$data['pages'] = array_filter(array_unique(GeneralUtility::intExplode(
			',',
			$data['pages'],
			TRUE
		)));
		$data['selected_categories'] = array_filter(array_unique(GeneralUtility::intExplode(
			',',
			$data['selected_categories'],
			TRUE
		)));
	}
}
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

use PatrickBroens\Contentelements\Utilities\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use PatrickBroens\Contentelements\Controller\ContentElementController;

/**
 * Class for rendering the content element "bullets"
 */
class BulletsRenderer extends AbstractContentElementRenderer {

	/**
	 * Render the content element "bullets"
	 *
	 * The field "bodytext" contains the bullet lines, separated by a line feed.
	 * It's transformed to an array before sending it to the view.
	 *
	 * For a definition list, there is also a second column for the description.
	 * A definition list will get a multidimensional array.
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
		if ((int)$data['bullets_type'] !== 2) {
			$data['bullets'] = GeneralUtility::trimExplode(LF, $data['bodytext']);
		} else {
			$data['bullets'] = ArrayUtility::csvToArray(
				$data['bodytext'],
				'|',
				'',
				2
			);
		}
	}
}
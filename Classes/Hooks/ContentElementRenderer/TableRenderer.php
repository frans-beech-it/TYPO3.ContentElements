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

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use PatrickBroens\Contentelements\Controller\ContentElementController;

/**
 * Class for rendering the content element "table"
 */
class TableRenderer extends AbstractContentElementRenderer {

	/**
	 * Render the content element "table"
	 *
	 * The table data is stored in the field "bodytext" as a string, where each line, separated by line feed,
	 * represents a row. By default columns are separated by the delimiter character "vertical line |",
	 * and can be enclosed (not default), like a regular CSV file.
	 *
	 * The table data is transformed to a multi dimensional array, taking the delimiter and enclosure into account,
	 * before it is passed to the view.
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
		$data['table'] = array();

		$delimiterCharacterCode = $data['table_delimiter'];

		if ($delimiterCharacterCode) {
			$delimiter = chr(intval($delimiterCharacterCode));
		} else {
			$delimiter = '|';
		}

		$enclosureCharacterCode = $data['table_enclosure'];

		if ($enclosureCharacterCode) {
			$enclosure = chr(intval($enclosureCharacterCode));
		} else {
			$enclosure = '';
		}

		$data['table']['data'] = ArrayUtility::csvToArray(
			$data['bodytext'],
			$delimiter,
			$enclosure,
			$data['cols']
		);
	}
}
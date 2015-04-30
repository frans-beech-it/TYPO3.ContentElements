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
 * A view helper which returns (multiple) selected content elements
 */
class RecordsViewHelper extends AbstractFrontendViewHelper {

	/**
	 * Render the TypoScript Object RECORDS
	 *
	 * @param string $source Comma separated uids of records
	 * @param string $tables Comma separated table names
	 * @return string
	 */
	public function render($source, $tables = 'tt_content') {

		$recordConfiguration = array(
			'tables' => $tables,
			'source' => $source,
			'dontCheckPid' => 1
		);

		return $this->contentObject->RECORDS($recordConfiguration);
    }
}
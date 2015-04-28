<?php
namespace PatrickBroens\Contentelements\Utilities;

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
 * Class ArrayUtility
 */
class ArrayUtility extends \TYPO3\CMS\Core\Utility\ArrayUtility {

	/**
	 * Convert a string, formatted as CSV, into an multidimensional array
	 *
	 * @param string $input The CSV input
	 * @param string $delimiter The delimiter
	 * @param string $enclosure The enclosure
	 * @param int $columns The maximum amount of columns
	 * @return array
	 */
	static public function csvToArray($input, $delimiter = ',', $enclosure = '"', $columns = 0) {
		$multiArray = array();
		$maximumCellCount = 0;

		$rows = GeneralUtility::trimExplode(LF, $input);

		foreach ($rows as $row) {
			$cells = str_getcsv($row, $delimiter, $enclosure);

			$maximumCellCount = max(count($cells), $maximumCellCount);

			$multiArray[] = $cells;
		}

		if ($columns > $maximumCellCount) {
			$maximumCellCount = $columns;
		}

		foreach ($multiArray as &$row) {
			for ($key = 0; $key < $maximumCellCount; $key++) {
				if ($columns > 0 && $columns < $maximumCellCount && $key >= $columns) {
					if (array_key_exists($key, $row)) {
						unset($row[$key]);
					}
				} else {
					if (!array_key_exists($key, $row)) {
						$row[$key] = '';
					}
				}
			}
		}

		return $multiArray;
	}
}
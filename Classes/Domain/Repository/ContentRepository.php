<?php
namespace PatrickBroens\Contentelements\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Patrick Broens <patrick@patrickbroens.nl>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * A repository for content
 */
class ContentRepository {

	/**
	 * The database connection
	 *
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $databaseConnection;

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
		$this->databaseConnection = $GLOBALS['TYPO3_DB'];
		$this->typoScriptFrontendController = $GLOBALS['TSFE'];
	}

	/**
	 * Find content with 'Show in Section Menus' enabled in a page
	 *
	 * By default only content in colPos=0 will be found. This can be overruled by using $column
	 *
	 * If you set property type to "all", then the 'Show in Section Menus' checkbox is not considered
	 * and all content elements are selected.
	 *
	 * If the property $type is 'header' then only content elements with a visible header layout
	 * (and a non-empty 'header' field!) is selected.
	 * In other words, if the header layout of an element is set to 'Hidden' then the page will not appear in the menu.
	 *
	 * @param int $pageUid The page uid
	 * @param string $type Search method
	 * @param int $column Restrict content by the column number
	 * @return bool|\mysqli_result|object MySQLi result object / DBAL object
	 */
	public function findBySection($pageUid, $type = '', $column = 0) {
		$constraints = array(
			'pid = ' . $pageUid,
			'colPos = ' . $column,
			'sys_language_uid = ' . $this->typoScriptFrontendController->sys_language_content
		);

		switch ($type) {
			case 'all':
				break;
			case 'header':
				$constraints[] = 'sectionIndex = 1';
				$constraints[] = 'header != \'\'';
				$constraints[] = 'header_layout != 100';
				break;
			default:
				$constraints[] = 'sectionIndex = 1';
		}

		$whereStatement = implode(' AND ', $constraints);
		$whereStatement .= $this->typoScriptFrontendController->sys_page->enableFields('tt_content', FALSE, array());

		return $this->databaseConnection->exec_SELECTgetRows(
			'*',
			'tt_content',
			$whereStatement
		);
	}
}
<?php
namespace PatrickBroens\Contentelements\ViewHelpers\Menu;

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

use TYPO3\CMS\Frontend\Page\PageRepository;
use PatrickBroens\Contentelements\ViewHelpers\AbstractFrontendViewHelper;

/**
 * Abstract for menu viewhelpers
 */
abstract class AbstractMenuViewHelper extends AbstractFrontendViewHelper {

	/**
	 * The page repository
	 *
	 * @var \TYPO3\CMS\Frontend\Page\PageRepository
	 */
	protected $pageRepository;

	/**
	 * The content repository
	 *
	 * @var \PatrickBroens\Contentelements\Domain\Repository\ContentRepository
	 * @inject
	 */
	protected $contentRepository;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->pageRepository = $this->typoScriptFrontendController->sys_page;
	}

	/**
	 * Get the constraints for the page based on doktype and field "nav_hide"
	 *
	 * By default the following doktypes are always ignored:
	 * - 6: Backend User Section
	 * - > 200: Folder (254)
	 *          Recycler (255)
	 *
	 * Optional are:
	 * - 199: Menu separator
	 * - nav_hide: Not in menu
	 *
	 * @param bool $includeNotInMenu Should pages which are hidden for menu's be included
	 * @param bool $includeMenuSeparator Should pages of type "Menu separator" be included
	 * @return string
	 */
	protected function getPageConstraints($includeNotInMenu = FALSE, $includeMenuSeparator = FALSE) {
		$constraints = array();

		$constraints[] = 'doktype != ' . PageRepository::DOKTYPE_BE_USER_SECTION;

		if (!$includeNotInMenu) {
			$constraints[] = 'nav_hide = 0';
		}

		if (!$includeMenuSeparator) {
			$constraints[] = 'doktype != ' . PageRepository::DOKTYPE_SPACER;
		}

		return 'AND ' . implode(' AND ', $constraints);
	}

	/**
	 * Find records from a certain table which have categories assigned
	 *
	 * @param array $categoryUids The uids of the categories
	 * @param string $relationField Field relation in MM table
	 * @param string $tableName Name of the table to search in
	 * @return array
	 */
	protected function findByCategories($categoryUids, $relationField, $tableName = 'pages') {
		$result = array();

		foreach ($categoryUids as $categoryUid) {
			try {
				$collection = \TYPO3\CMS\Frontend\Category\Collection\CategoryCollection::load(
					$categoryUid,
					TRUE,
					$tableName,
					$relationField
				);
				if ($collection->count() > 0) {
					foreach ($collection as $record) {
						$result[$record['uid']] = $record;
					}
				}
			} catch (\Exception $e) {

			}
		}

		return $result;
	}
}
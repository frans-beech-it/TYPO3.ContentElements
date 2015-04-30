<?php
namespace PatrickBroens\Contentelements\Page;

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
 * Extend of Page functions, a lot of sql/pages-related functions
 * Back port of https://review.typo3.org/#/c/38258/
 */
class PageRepository extends \TYPO3\CMS\Frontend\Page\PageRepository {


	/**
	 * Returns the relevant page overlay record fields
	 *
	 * @param mixed $pageInput If $pageInput is an integer, it's the pid of the pageOverlay record and thus the page overlay record is returned. If $pageInput is an array, it's a page-record and based on this page record the language record is found and OVERLAYED before the page record is returned.
	 * @param int $lUid Language UID if you want to set an alternative value to $this->sys_language_uid which is default. Should be >=0
	 * @throws \UnexpectedValueException
	 * @return array Page row which is overlayed with language_overlay record (or the overlay record alone)
	 */
	public function getPageOverlay($pageInput, $lUid = -1) {
		$rows = $this->getPagesOverlay(array($pageInput), $lUid);
		// Always an array in return
		return count($rows) ? $rows[0] : array();
	}

	/**
	 * Returns the relevant page overlay record fields
	 *
	 * @param array $pagesInput Array of integers or array of arrays. If each value is an integer, it's the pids of the pageOverlay records and thus the page overlay records are returned. If each value is an array, it's page-records and based on this page records the language records are found and OVERLAYED before the page records are returned.
	 * @param int $lUid Language UID if you want to set an alternative value to $this->sys_language_uid which is default. Should be >=0
	 * @throws \UnexpectedValueException
	 * @return array Page rows which are overlayed with language_overlay record.
	 *			   If the input was an array of integers, missing records are not
	 *			   included. If the input were page rows, untranslated pages
	 *			   are returned.
	 */
	public function getPagesOverlay(array $pagesInput, $lUid = -1) {
		if (count($pagesInput) == 0) {
			return array();
		}
		// Initialize:
		if ($lUid < 0) {
			$lUid = $this->sys_language_uid;
		}
		$row = NULL;
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay'])) {
			foreach ($pagesInput as $origPage) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPageOverlay'] as $classRef) {
					$hookObject = GeneralUtility::makeInstance($classRef);
					if (!$hookObject instanceof PageRepositoryGetPageOverlayHookInterface) {
						throw new \UnexpectedValueException('$hookObject must implement interface ' . PageRepositoryGetPageOverlayHookInterface::class, 1269878881);
					}
					$hookObject->getPageOverlay_preProcess($origPage, $lUid, $this);
				}
			}
		}
		// If language UID is different from zero, do overlay:
		if ($lUid) {
			$fieldArr = GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['FE']['pageOverlayFields'], TRUE);
			$page_ids = array();

			$origPage = reset($pagesInput);
			if (is_array($origPage)) {
				// Make sure that only fields which exist in the first incoming record are overlaid!
				$fieldArr = array_intersect($fieldArr, array_keys($origPage));
			}
			foreach ($pagesInput as $origPage) {
				if (is_array($origPage)) {
					// Was the whole record
					$page_ids[] = $origPage['uid'];
				} else {
					// Was the id
					$page_ids[] = $origPage;
				}
			}
			if (count($fieldArr)) {
				if (!in_array('pid', $fieldArr)) {
					$fieldArr[] = 'pid';
				}
				// NOTE to enabledFields('pages_language_overlay'):
				// Currently the showHiddenRecords of TSFE set will allow
				// pages_language_overlay records to be selected as they are
				// child-records of a page.
				// However you may argue that the showHiddenField flag should
				// determine this. But that's not how it's done right now.
				// Selecting overlay record:
				$res = $this->getDatabaseConnection()->exec_SELECTquery(
					implode(',', $fieldArr),
					'pages_language_overlay',
					'pid IN(' . implode(',', $this->getDatabaseConnection()->cleanIntArray($page_ids)) . ')'
					. ' AND sys_language_uid=' . (int)$lUid . $this->enableFields('pages_language_overlay'),
					'',
					''
				);
				$overlays = array();
				while ($row = $this->getDatabaseConnection()->sql_fetch_assoc($res)) {
					$this->versionOL('pages_language_overlay', $row);
					if (is_array($row)) {
						$row['_PAGES_OVERLAY'] = TRUE;
						$row['_PAGES_OVERLAY_UID'] = $row['uid'];
						$row['_PAGES_OVERLAY_LANGUAGE'] = $lUid;
						$origUid = $row['pid'];
						// Unset vital fields that are NOT allowed to be overlaid:
						unset($row['uid']);
						unset($row['pid']);
						$overlays[$origUid] = $row;
					}
				}
				$this->getDatabaseConnection()->sql_free_result($res);
			}
		}
		// Create output:
		$pagesOutput = array();
		foreach ($pagesInput as $key => $origPage) {
			if (is_array($origPage)) {
				$pagesOutput[$key] = $origPage;
				if (isset($overlays[$origPage['uid']])) {
					// Overwrite the original field with the overlay
					foreach ($overlays[$origPage['uid']] as $fieldName => $fieldValue) {
						if ($fieldName !== 'uid' && $fieldName !== 'pid') {
							if ($this->shouldFieldBeOverlaid('pages_language_overlay', $fieldName, $fieldValue)) {
								$pagesOutput[$key][$fieldName] = $fieldValue;
							}
						}
					}
				}
			} else {
				if (isset($overlays[$origPage])) {
					$pagesOutput[$key] = $overlays[$origPage];
				}
			}
		}
		return $pagesOutput;
	}

	/**
	 * Returns an array with pagerows for subpages with pid=$uid (which is pid
	 * here!). This is used for menus. If there are mount points in overlay mode
	 * the _MP_PARAM field is set to the corret MPvar.
	 *
	 * If the $uid being input does in itself require MPvars to define a correct
	 * rootline these must be handled externally to this function.
	 *
	 * @param int|int[] $uid The page id (or array of page ids) for which to fetch subpages (PID)
	 * @param string $fields List of fields to select. Default is "*" = all
	 * @param string $sortField The field to sort by. Default is "sorting
	 * @param string $addWhere Optional additional where clauses. Like "AND title like '%blabla%'" for instance.
	 * @param bool $checkShortcuts Check if shortcuts exist, checks by default
	 * @return array Array with key/value pairs; keys are page-uid numbers. values are the corresponding page records (with overlayed localized fields, if any)
	 * @see \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::getPageShortcut(), \TYPO3\CMS\Frontend\ContentObject\Menu\AbstractMenuContentObject::makeMenu()
	 * @see \TYPO3\CMS\WizardCrpages\Controller\CreatePagesWizardModuleFunctionController, \TYPO3\CMS\WizardSortpages\View\SortPagesWizardModuleFunction
	 */
	public function getMenu($uid, $fields = '*', $sortField = 'sorting', $addWhere = '', $checkShortcuts = TRUE) {
		$pages = array();

		$whereStatement = '
			pid IN (' . implode(',', $this->getDatabaseConnection()->cleanIntArray((array)$uid)) . ')'
			. $this->where_hid_del
			. $this->where_groupAccess
			. ' ' . $addWhere;

		$databaseResource = $this->getDatabaseConnection()->exec_SELECTquery(
			$fields,
			'pages',
			$whereStatement,
			'',
			$sortField
		);

		while (($page = $this->getDatabaseConnection()->sql_fetch_assoc($databaseResource))) {
			$originalUid = $page['uid'];

			// Versioning Preview Overlay
			$this->versionOL('pages', $page, TRUE);

			// Add a mount point parameter if needed
			$this->addMountPointParameter($page);

			// If shortcut, look up if the target exists and is currently visible
			if ($checkShortcuts) {
				$this->checkShortcutVisibility($page, $addWhere);
			}

			// If the page still is there, we add it to the output
			if (is_array($page)) {
				$pages[$originalUid] = $page;
			}
		}

		$this->getDatabaseConnection()->sql_free_result($databaseResource);

		// Finally load language overlays
		return $this->getPagesOverlay($pages);
	}

	/**
	 * Returns an array with pagerows for current pages with uid=$uid
	 * This is used for menus. If there are mount points in overlay mode
	 * the _MP_PARAM field is set to the correct MPvar.
	 *
	 * @param int|int[] $uid The page id (or array of page ids) for which to fetch
	 * @param string $fields List of fields to select. Default is "*" = all
	 * @param string $sortField The field to sort by. Default is "sorting
	 * @param string $addWhere Optional additional where clauses. Like "AND title like '%blabla%'" for instance.
	 * @param bool $checkShortcuts Check if shortcuts exist, checks by default
	 * @return array Array with key/value pairs; keys are page-uid numbers. values are the corresponding page records (with overlayed localized fields, if any)
	 */
	public function getMenuList($pageUids, $fields = '*', $sortField = 'sorting', $addWhere = '', $checkShortcuts = TRUE) {
		$pages = array();

		$whereStatement = '
			uid IN (' . implode(',', $this->getDatabaseConnection()->cleanIntArray((array)$pageUids)) . ')'
			. $this->where_hid_del
			. $this->where_groupAccess
			. ' ' . $addWhere;

		$databaseResource = $this->getDatabaseConnection()->exec_SELECTquery(
			$fields,
			'pages',
			$whereStatement,
			'',
			$sortField
		);

		while (($page = $this->getDatabaseConnection()->sql_fetch_assoc($databaseResource))) {
			$originalUid = $page['uid'];

			// Versioning Preview Overlay
			$this->versionOL('pages', $page, TRUE);

			// Add a mount point parameter if needed
			$this->addMountPointParameter($page);

			// If shortcut, look up if the target exists and is currently visible
			if ($checkShortcuts) {
				$this->checkShortcutVisibility($page, $addWhere);
			}

			// If the page still is there, we add it to the output
			if (is_array($page)) {
				$pages[$originalUid] = $page;
			}
		}

		$this->getDatabaseConnection()->sql_free_result($databaseResource);

		// Finally load language overlays
		return $this->getPagesOverlay($pages);
	}

	/**
	 * Add the mount point parameter to the page if needed
	 *
	 * @param array $page The page to check
	 */
	protected function addMountPointParameter(&$page) {
		if (!is_array($page)) {
			return;
		}

		// $page MUST have "uid", "pid", "doktype", "mount_pid", "mount_pid_ol" fields
		// in it
		$mountPointInfo = $this->getMountPointInfo($page['uid'], $page);

		// There is a valid mount point.
		if (is_array($mountPointInfo) && $mountPointInfo['overlay']) {

			// Using "getPage" is OK since we need the check for enableFields AND for type 2
			// of mount pids we DO require a doktype < 200!
			$mountPointPage = $this->getPage($mountPointInfo['mount_pid']);

			if (count($mountPointPage)) {
				$page = $mountPointPage;
				$page['_MP_PARAM'] = $mountPointInfo['MPvar'];
			} else {
				unset($page);
			}
		}
	}

	/**
	 * If shortcut, look up if the target exists and is currently visible
	 *
	 * @param array $page The page to check
	 * @param string $addWhere Optional additional where clauses. Like "AND title like '%blabla%'" for instance.
	 */
	protected function checkShortcutVisibility(&$page, $addWhere) {
		if (!is_array($page)) {
			return;
		}

		if ($page['doktype'] == self::DOKTYPE_SHORTCUT && ($page['shortcut'] || $page['shortcut_mode'])) {
			if ($page['shortcut_mode'] == self::SHORTCUT_MODE_NONE) {
				// No shortcut_mode set, so target is directly set in $page['shortcut']
				$searchField = 'uid';
				$searchUid = (int)$page['shortcut'];

			} elseif ($page['shortcut_mode'] == self::SHORTCUT_MODE_FIRST_SUBPAGE || $page['shortcut_mode'] == self::SHORTCUT_MODE_RANDOM_SUBPAGE) {
				// Check subpages - first subpage or random subpage
				$searchField = 'pid';
				// If a shortcut mode is set and no valid page is given to select subpags
				// from use the actual page.
				$searchUid = (int)$page['shortcut'] ?: $page['uid'];

			} elseif ($page['shortcut_mode'] == self::SHORTCUT_MODE_PARENT_PAGE) {
				// Shortcut to parent page
				$searchField = 'uid';
				$searchUid = $page['pid'];
			}

			$whereStatement = $searchField . '=' . $searchUid
				. $this->where_hid_del
				. $this->where_groupAccess
				. ' ' . $addWhere;

			$count = $this->getDatabaseConnection()->exec_SELECTcountRows(
				'uid',
				'pages',
				$whereStatement
			);

			if (!$count) {
				unset($page);
			}
		} elseif ($page['doktype'] == self::DOKTYPE_SHORTCUT) {
			// Neither shortcut target nor mode is set. Remove the page from the menu.
			unset($page);
		}
	}

	/**
	 * Returns the database connection
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	protected function getTypoScriptFrontendController() {
		return $GLOBALS['TSFE'];
	}
}

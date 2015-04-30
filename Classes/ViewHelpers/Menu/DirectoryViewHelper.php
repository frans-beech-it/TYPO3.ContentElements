<?php
namespace PatrickBroens\Contentelements\ViewHelpers\Menu;

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
 * A view helper which returns the subpages of the given pages
 *
 * = Example =
 *
 * <code title="Directory of pages with uid = 1 and uid = 2">
 * <ce:menu.directory pageUids="{0: 1, 1: 2}" as="pages">
 *   <f:for each="{pages}" as="page">
 *     {page.title}
 *   </f:for>
 * </ce:menu.directory>
 * </code>
 *
 * <output>
 * Subpage 1 of page with uid = 1
 * Subpage 2 of page with uid = 1
 * Subpage 1 of page with uid = 2
 * </output>
 */
class DirectoryViewHelper extends AbstractMenuViewHelper {

	/**
	 * Render the view helper
	 *
	 * @param string $as The name of the iteration variable
	 * @param array $pageUids The page uids of the parent pages
	 * @param int $entryLevel The entry level
	 * @param string $level The name of the level variable
	 * @param int $maximumLevel The maximum level for the menu, if nested
	 * @param bool $includeNotInMenu Should pages which are hidden for menu's be included
	 * @param bool $includeMenuSeparator Should pages of type "Menu separator" be included
	 * @return string
	 */
	public function render(
		$as,
		$pageUids = array(),
		$entryLevel = NULL,
		$level = 'level',
		$maximumLevel = 10,
		$includeNotInMenu = FALSE,
		$includeMenuSeparator = FALSE
	) {

		// If no pages have been defined, use the current page
		if (empty($pageUids)) {
			if ($entryLevel !== NULL) {
				if ($entryLevel < 0) {
					$entryLevel = count($this->typoScriptFrontendController->tmpl->rootLine) - 1 + $entryLevel;
				}
				$pageUids = array($this->typoScriptFrontendController->tmpl->rootLine[$entryLevel]['uid']);
			} else {
				$pageUids = array($this->typoScriptFrontendController->id);
			}
		}

		// Remove empty entries from array
		$pageUids = array_filter($pageUids);

		$pages = $this->pageRepository->getMenu(
			$pageUids,
			'*',
			'sorting',
			$this->getPageConstraints($includeNotInMenu, $includeMenuSeparator)
		);

		$output = '';

		if (!empty($pages)) {

			if (!$this->typoScriptFrontendController->register['ceMenuLevel']) {
				$this->typoScriptFrontendController->register['ceMenuLevel'] = 1;
				$this->typoScriptFrontendController->register['ceMenuMaximumLevel'] = $maximumLevel;
			} else {
				$this->typoScriptFrontendController->register['ceMenuLevel']++;
			}

			if ($this->typoScriptFrontendController->register['ceMenuLevel'] > $this->typoScriptFrontendController->register['ceMenuMaximumLevel']) {
				return '';
			}

			$this->templateVariableContainer->add($level, $this->typoScriptFrontendController->register['ceMenuLevel']);
			$this->templateVariableContainer->add($as, $pages);
			$output = $this->renderChildren();
			$this->templateVariableContainer->remove($as);
			$this->templateVariableContainer->remove($level);

			$this->typoScriptFrontendController->register['ceMenuLevel']--;

			if ($this->typoScriptFrontendController->register['ceMenuLevel'] === 0) {
				unset($this->typoScriptFrontendController->register['ceMenuLevel']);
				unset($this->typoScriptFrontendController->register['ceMenuMaximumLevel']);
			}
		}

		return $output;
	}
}
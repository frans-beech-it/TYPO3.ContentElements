<?php
namespace PatrickBroens\Contentelements\ViewHelpers\Attribute;

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

use PatrickBroens\Contentelements\ViewHelpers\AbstractFrontendViewHelper;

/**
 * A view helper which returns a class of combined names
 *
 * = Example =
 *
 * <code>
 * <ce:attribute.class names="{0: 'className-1', 1: 'className-2'}" />
 * </code>
 *
 * <output>
 * class="className-1 className-2"
 * </output>
 */
class ClassViewHelper extends AbstractFrontendViewHelper {

	/**
	 * Render the view helper
	 *
	 * @param array $names Names of the classes
	 * @return string Rendered class attribute
	 */
	public function render(array $names) {
		if (array_filter($names)) {
			return 'class="' . htmlspecialchars(trim(implode(' ', $names))) . '"';
		}
	}
}
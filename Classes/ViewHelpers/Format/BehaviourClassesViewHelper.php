<?php
namespace PatrickBroens\Contentelements\ViewHelpers\Format;

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
 * A view helper which returns a string with classes mapped from field behaviour
 */
class BehaviourClassesViewHelper extends AbstractFrontendViewHelper {

	/**
	 * Render the view helper
	 *
	 * @param string $keys The keys from the field "behaviour" in the database, comma separated
	 * @param array $mapping The mapping from the keys to the class names
	 * @param string $delimiter The delimiter for the separate classes
	 * @return string|NULL Concatenated class names
	 */
	public function render($keys = '', $mapping = array(), $delimiter = ' ') {
		$classes = '';

		if (!empty($keys) && !empty($mapping)) {
			$selected = array_intersect_key($mapping, array_flip(explode(',', $keys)));

			$classes = htmlspecialchars(trim(implode($delimiter, $selected)));
		}

		return $classes;
	}
}
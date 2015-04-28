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

use TYPO3\CMS\Fluid\View\StandaloneView;
use PatrickBroens\Contentelements\Controller\ContentElementController;

/**
 * Interface for classes which hook into ContentElementController
 * to render types of content elements
 */
interface ContentElementRendererHookInterface {

	/**
	 * Render a content element
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
	);
}
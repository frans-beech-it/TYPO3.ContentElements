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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use PatrickBroens\Contentelements\Controller\ContentElementController;

/**
 * Class for rendering the content element "textmedia"
 */
class TextMediaRenderer extends AbstractContentElementRenderer {

	/**
	 * Render the content element "textmedia"
	 *
	 * Files are FAL references, the amount mentioned in the field "image".
	 * The files are collected by these references before sending it to the view.
	 *
	 * Sets the gallery position as variables, for better usability in Fluid templates.
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
		$fileRepository = $controller->objectManager->get(\TYPO3\CMS\Core\Resource\FileRepository::class);
		$unFilteredFileObjects = $fileRepository->findByRelation('tt_content', 'image', $data['uid']);
		$fileObjects = $this->filterFileObjectsWithWidthAndHeight($unFilteredFileObjects);

		$data['media'] = $fileObjects;

		$this->galleryPosition($data);
		$this->galleryWidth($data, $settings);
	}

	/**
	 * Filter the file objects which have a defined width and height
	 *
	 * For the gallery we can only use file objects which have a defined width and height
	 * File objects which do not have this defined are unset
	 *
	 * @param array $fileObjects The unfiltered file object
	 * @return array The filtered file objects
	 */
	protected function filterFileObjectsWithWidthAndHeight(array $fileObjects) {
		foreach ($fileObjects as $key => $fileObject) {
			if ($fileObject->getProperty('width') === 0 || $fileObject->getProperty('height') === 0) {
				unset($fileObjects[$key]);
			}
		}

		return $fileObjects;
	}

	/**
	 * Define the gallery position, based on field "imageorient"
	 *
	 * Gallery has a horizontal and a vertical position towards the text
	 * and a possible wrapping of the text around the gallery.
	 *
	 * @param array $data The data of the content element (row in tt_content table)
	 * @return void
	 */
	protected function galleryPosition(array &$data) {
		$galleryPosition = array(
			'horizontal' => array(
				'center' => array(0, 8),
				'right' => array(1, 9, 17, 25),
				'left' => array(2, 10, 18, 26)
			),
			'vertical' => array(
				'above' => array(0, 1, 2),
				'intext' => array(17, 18, 25, 26),
				'below' => array(8, 9, 10)
			)
		);

		foreach ($galleryPosition as $positionDirectionKey => $positionDirectionValue) {
			foreach ($positionDirectionValue as $positionKey => $positionArray) {
				if (in_array((int)$data['imageorient'], $positionArray, TRUE)) {
					$data['galleryPosition'][$positionDirectionKey] = $positionKey;
				}
			}
		}

		if (in_array((int)$data['imageorient'], array(25, 26), TRUE)) {
			$data['galleryPosition']['noWrap'] = TRUE;
		}
	}

	/**
	 * Set the gallery width based on vertical position and register settings
	 *
	 * @param array $data The data of the content element (row in tt_content table)
	 * @param array $settings The view settings
	 * @return void
	 */
	protected function galleryWidth(array &$data, array $settings) {
		if ($data['galleryPosition']['vertical'] === 'intext') {
			if ($this->typoScriptFrontendController->register['maxImageWidthInText']) {
				$data['galleryWidth'] = $this->typoScriptFrontendController->register['maxImageWidthInText'];
			} else {
				$data['galleryWidth'] = $settings['media']['gallery']['maximumImageWidthInText'];
			}
		} else {
			if ($this->typoScriptFrontendController->register['maxImageWidth']) {
				$data['galleryWidth'] = $this->typoScriptFrontendController->register['maxImageWidth'];
			} else {
				$data['galleryWidth'] = $settings['media']['gallery']['maximumImageWidth'];
			}
		}
	}
}
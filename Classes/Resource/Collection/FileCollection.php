<?php
namespace PatrickBroens\Contentelements\Resource\Collection;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class to collect files from various sources
 *
 * Sources can be file references, file collections or folders
 */
class FileCollection {

	/**
	 * The files
	 *
	 * @var array
	 */
	protected $files = array();

	/**
	 * The file repository
	 *
	 * @var \TYPO3\CMS\Core\Resource\FileRepository
	 */
	protected $fileRepository;

	/**
	 * The file collection repository
	 *
	 * @var \TYPO3\CMS\Core\Resource\FileCollectionRepository
	 */
	protected $fileCollectionRepository;

	/**
	 * The resource factory
	 *
	 * @var \TYPO3\CMS\Core\Resource\ResourceFactory
	 */
	protected $resourceFactory;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->fileRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\FileRepository::class);
		$this->fileCollectionRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\FileCollectionRepository::class);
		$this->resourceFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
	}

	/**
	 * Get all the files from relation, file collections and folders, sorted by property
	 *
	 * @param int $relationUid The uid of the content element to fetch the files for
	 * @param string $relationField The field which holds the files
	 * @param string $fileCollectionUids The file collections uids, comma separated
	 * @param string $folderIdentifiers The folder identifiers, comma separated
	 * @param string $sortingProperty The property to sort the files with
	 * @return array The files
	 */
	public function getAllSorted(
		$relationUid = NULL,
		$relationField = 'media',
		$fileCollectionUids = '',
		$folderIdentifiers = '',
		$sortingProperty = ''
	) {
		$this->addAll($relationUid, $relationField, $fileCollectionUids, $folderIdentifiers);

		$this->sort($sortingProperty);

		return $this->files;
	}

	/**
	 * Get all the files from relation, file collections and folders, in order of incoming values
	 *
	 * @param int $relationUid The uid of the content element to fetch the files for
	 * @param string $relationField The field which holds the files
	 * @param string $fileCollectionUids The file collections uids, comma separated
	 * @param string $folderIdentifiers The folder identifiers, comma separated
	 * @return array The files
	 */
	public function getAll(
		$relationUid = NULL,
		$relationField = 'media',
		$fileCollectionUids = '',
		$folderIdentifiers = ''
	) {
		$this->addAll($relationUid, $relationField, $fileCollectionUids, $folderIdentifiers);

		return $this->files;
	}

	/**
	 * Add files to the collection from relation, file collections and folders
	 *
	 * @param int $relationUid The uid of the content element to fetch the files for
	 * @param string $relationField The field which holds the files
	 * @param string $fileCollectionUids The file collections uids, comma separated
	 * @param string $folderIdentifiers The folder identifiers, comma separated
	 * @return void
	 */
	public function addAll($relationUid = NULL, $relationField = 'media', $fileCollectionUids = '', $folderIdentifiers = '') {
		$this->addFilesFromRelation($relationUid, $relationField);
		$this->addFilesFromFileCollections($fileCollectionUids);
		$this->addFilesFromFolders($folderIdentifiers);
	}

	/**
	 * Add files to the collection from a relation
	 *
	 * @param int $relationUid The uid of the content element to fetch the files for
	 * @param string $relationField The field which holds the files
	 * @return void
	 */
	public function addFilesFromRelation($relationUid = NULL, $relationField = 'media') {
		if ($relationUid && $relationField !== '') {
			$files = $this->fileRepository->findByRelation('tt_content', $relationField, $relationUid);

			$this->addMultiple($files);
		}
	}

	/**
	 * Add files to the collection from multiple file collections
	 *
	 * @param string $fileCollectionUids The file collections uids, comma separated
	 * @return void
	 */
	public function addFilesFromFileCollections($fileCollectionUids = '') {
		$fileCollectionUids = GeneralUtility::intExplode(',', $fileCollectionUids, TRUE);

		if (!empty($fileCollectionUids)) {
			foreach ($fileCollectionUids as $fileCollectionUid) {
				$this->addFilesFromFileCollection($fileCollectionUid);
			}
		}
	}

	/**
	 * Add files to the collection from one single file collection
	 *
	 * @param int $fileCollectionUid The file collections uid
	 * @return void
	 */
	public function addFilesFromFileCollection($fileCollectionUid = NULL) {
		if (!empty($fileCollectionUid)) {
			try {
				$fileCollection = $this->fileCollectionRepository->findByUid($fileCollectionUid);

				if ($fileCollection instanceof \TYPO3\CMS\Core\Resource\Collection\AbstractFileCollection) {
					$fileCollection->loadContents();
					$files = $fileCollection->getItems();

					$this->addMultiple($files);
				}
			} catch (\TYPO3\CMS\Core\Resource\Exception $e) {
				$logger = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger();
				$logger->warning(
					'The file-collection with uid  "' . $fileCollectionUid
					. '" could not be found or contents could not be loaded and won\'t be included in frontend output.'
					. '(' . $e->getMessage() . ')'
				);
			}
		}
	}

	/**
	 * Add files to the collection from multiple folders
	 *
	 * @param string $folderIdentifiers The folder identifiers, comma separated
	 * @return void
	 */
	public function addFilesFromFolders($folderIdentifiers) {
		$folderIdentifiers = GeneralUtility::trimExplode(',', $folderIdentifiers);

		foreach ($folderIdentifiers as $folderIdentifier) {
			$this->addFilesFromFolder($folderIdentifier);
		}
	}

	/**
	 * Add files to the collection from one single folder
	 *
	 * @param string $folderIdentifier The folder identifier
	 */
	public function addFilesFromFolder($folderIdentifier) {
		if ($folderIdentifier) {
			try {
				$folder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($folderIdentifier);
				if ($folder instanceof \TYPO3\CMS\Core\Resource\Folder) {
					$files = $folder->getFiles();

					$this->addMultiple($files);
				}
			} catch (\TYPO3\CMS\Core\Resource\Exception $e) {
				$logger = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger();
				$logger->warning('The folder with identifier  "' . $folderIdentifier . '" could not be found and won\'t be included in frontend output');
			}
		}
	}

	/**
	 * Sort the file objects based on a property
	 *
	 * @param string $sortingProperty The sorting property
	 * @return void
	 */
	public function sort($sortingProperty = '') {
		if ($sortingProperty !== '' && count($this->files) > 1) {
			usort(
				$this->files,
				function(
					\TYPO3\CMS\Core\Resource\FileInterface $a,
					\TYPO3\CMS\Core\Resource\FileInterface $b
				) use($sortingProperty) {
					if ($a->hasProperty($sortingProperty) && $b->hasProperty($sortingProperty)) {
						return strnatcasecmp($a->getProperty($sortingProperty), $b->getProperty($sortingProperty));
					} else {
						return 0;
					}
				}
			);
		}
	}

	/**
	 * Add a file object to the collection
	 *
	 * @param mixed $file The file object
	 * @return void
	 */
	public function add($file) {
		$this->files[] = $file;
	}

	/**
	 * Add multiple file objects to the collection
	 *
	 * @param mixed $files The file objects
	 * @return void
	 */
	public function addMultiple($files) {
		$this->files = array_merge($this->files, $files);
	}
}
<?php
/**
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        22.12.2015
 */
namespace BiberLtd\Bundle\FileManagementBundle\Services;

use BiberLtd\Bundle\CoreBundle\CoreModel;
use BiberLtd\Bundle\FileManagementBundle\Entity as BundleEntity;
use BiberLtd\Bundle\FileManagementBundle\Exception\InvalidFileTypeException;
use BiberLtd\Bundle\SiteManagementBundle\Services as SMMService;
use BiberLtd\Bundle\CoreBundle\Services as CoreServices;
use BiberLtd\Bundle\CoreBundle\Responses\ModelResponse;
use BiberLtd\Bundle\CoreBundle\Exceptions as CoreExceptions;

class FileManagementModel extends CoreModel {
	/**
	 * FileManagementModel constructor.
	 *
	 * @param object      $kernel
	 * @param string|null $dbConnection
	 * @param string|null $orm
	 */
    public function __construct($kernel, string $dbConnection = null, string $orm = null) {
        parent::__construct($kernel, $dbConnection ?? 'default', $orm ?? 'doctrine');

        $this->entity = array(
            'f' => array('name' => 'FileManagementBundle:File', 'alias' => 'f'),
            'fl' => array('name' => 'FileManagementBundle:FileLocalization', 'alias' => 'fl'),
            'fuf' => array('name' => 'FileManagementBundle:FileUploadFolder', 'alias' => 'fuf'),
        );
    }

	/**
	 * Destructor
	 */
    public function __destruct() {
        foreach ($this as $property => $value) {
            $this->$property = null;
        }
    }

	/**
	 * @param mixed $file
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function deleteFile($file){
		return $this->deleteFiles(array($file));
	}

	/**
	 * @param array $collection
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function deleteFiles(array $collection) {
		$timeStamp = microtime();
		$countDeleted = 0;
		foreach($collection as $entry){
			if($entry instanceof BundleEntity\File){
				$this->em->remove($entry);
				$countDeleted++;
			}
			else{
				$response = $this->getFile($entry);
				if(!$response->error->exist){
					$entry = $response->result->set;
					$this->em->remove($entry);
					$countDeleted++;
				}
			}
		}
		if($countDeleted < 0){
			return new ModelResponse(null, 0, 0, null, true, 'E:E:001', 'Unable to delete all or some of the selected entries.', $timeStamp, microtime());
		}
		$this->em->flush();

		return new ModelResponse(null, 0, 0, null, false, 'S:D:001', 'Selected entries have been successfully removed from database.', $timeStamp, microtime());
	}

	/**
	 * @param mixed $folder
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function deleteFileUploadFolder($folder){
		return $this->deleteFileUploadFolders(array($folder));
	}

	/**
	 * @param array $collection
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function deleteFileUploadFolders(array $collection) {
		$timeStamp = microtime();
		$countDeleted = 0;
		foreach($collection as $entry){
			if($entry instanceof BundleEntity\FileUploadFolder){
				$this->em->remove($entry);
				$countDeleted++;
			}
			else{
				$response = $this->getFileUploadFolder($entry);
				if(!$response->error->exist){
					$entry = $response->result->set;
					$this->em->remove($entry);
					$countDeleted++;
				}
			}
		}
		if($countDeleted < 0){
			return new ModelResponse(null, 0, 0, null, true, 'E:E:001', 'Unable to delete all or some of the selected entries.', $timeStamp, microtime());
		}
		$this->em->flush();

		return new ModelResponse(null, 0, 0, null, false, 'S:D:001', 'Selected entries have been successfully removed from database.', $timeStamp, microtime());
	}

	/**
	 * @param mixed $file
	 * @param bool $bypass
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|bool
	 */
	public function doesFileExist($file, bool $bypass = false) {
		$timeStamp = microtime();
		$exist = false;

		$response = $this->getFile($file);

		if ($response->error->exist) {
			if($bypass){
				return $exist;
			}
			$response->result->set = false;
			return $response;
		}

		$exist = true;

		if ($bypass) {
			return $exist;
		}
		return new ModelResponse(true, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime());
	}

	/**
	 * @param mixed $folder
	 * @param bool $bypass
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|bool
	 */
	public function doesFileUploadFolderExist($folder, bool $bypass = false) {
		$timeStamp = microtime();
		$exist = false;

		$response = $this->getFileUploadFolder($folder);

		if ($response->error->exist) {
			if($bypass){
				return $exist;
			}
			$response->result->set = false;
			return $response;
		}

		$exist = true;

		if ($bypass) {
			return $exist;
		}
		return new ModelResponse(true, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime());
	}

	/**
	 * @param mixed $file
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function getFile($file) {
		$timeStamp = microtime();
		if($file instanceof BundleEntity\File){
			return new ModelResponse($file, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime());
		}
		$result = null;
		switch($file){
			case is_numeric($file):
				$result = $this->em->getRepository($this->entity['f']['name'])->findOneBy(array('id' => $file));
				break;
			case is_string($file):
				$result = $this->em->getRepository($this->entity['f']['name'])->findOneBy(array('url_key' => $file));
				if(is_null($result)){
					$result = $this->em->getRepository($this->entity['f']['name'])->findOneBy(array('source_original' => $file));
					if(is_null($result)){
						$result = $this->em->getRepository($this->entity['f']['name'])->findOneBy(array('source_preview' => $file));
					}
				}
				break;
		}
		if(is_null($result)){
			return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, microtime());
		}

		return new ModelResponse($result, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime());
	}

	/**
	 * @param mixed $folder
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function getFileUploadFolder($folder) {
		$timeStamp = microtime();
		if($folder instanceof BundleEntity\FileUploadFolder){
			return new ModelResponse($folder, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime());
		}
		$result = null;
		switch($folder){
			case is_numeric($folder):
				$result = $this->em->getRepository($this->entity['fuf']['name'])->findOneBy(array('id' => $folder));
				break;
			case is_string($folder):
				$result = $this->em->getRepository($this->entity['fuf']['name'])->findOneBy(array('url_key' => $folder));
				break;
		}
		if(is_null($result)){
			return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, microtime());
		}

		return new ModelResponse($result, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime());
	}

	/**
	 * @param mixed $file
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function insertFile($file) {
        return $this->insertFiles(array($file));
    }

	/**
	 * @param array $collection
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertFiles(array $collection)	{
		$timeStamp = microtime();
		/** Parameter must be an array */
		$countInserts = 0;
		$countLocalizations = 0;
		$insertedItems = [];
		$localizations = [];
		foreach ($collection as $data) {
			if ($data instanceof BundleEntity\File) {
				$entity = $data;
				$this->em->persist($entity);
				$insertedItems[] = $entity;
				$countInserts++;
			}
			else if (is_object($data)) {
				$entity = new BundleEntity\File;
				if(!property_exists($data, 'site')){
					$data->site = 1;
				}
				if(!property_exists($data, 'folder')){
					$data->folder = 1;
				}
				foreach ($data as $column => $value) {
					$localeSet = false;
					$set = 'set' . $this->translateColumnName($column);
					switch ($column) {
						case 'local':
							$localizations[$countInserts]['localizations'] = $value;
							$localeSet = true;
							$countLocalizations++;
							break;
						case 'site':
							$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
							$response = $sModel->getSite($value);
							if(!$response->error->exist){
								$entity->$set($response->result->set);
							}
							unset($response, $sModel);
							break;
						case 'folder':
							$response = $this->getFileUploadFolder($value);
							if(!$response->error->exist){
								$entity->$set($response->result->set);
							}
							unset($response);
							break;
						default:
							$entity->$set($value);
							break;
					}
					if ($localeSet) {
						$localizations[$countInserts]['entity'] = $entity;
					}
				}
				$this->em->persist($entity);
				$insertedItems[] = $entity;

				$countInserts++;
			}
		}
		if ($countInserts > 0) {
			$this->em->flush();
		}
		/** Now handle localizations */
		if ($countInserts > 0 && $countLocalizations > 0) {
			$response = $this->insertFileLocalizations($localizations);
		}
		if($countInserts > 0){
			$this->em->flush();
			return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, microtime());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, microtime());
	}

	/**
	 * @param array $collection
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertFileLocalizations(array $collection) {
		$timeStamp = microtime();
		$countInserts = 0;
		$insertedItems = [];
		foreach($collection as $data){
			if($data instanceof BundleEntity\FileLocalization){
				$entity = $data;
				$this->em->persist($entity);
				$insertedItems[] = $entity;
				$countInserts++;
			}
			else{
				$file = $data['entity'];
				foreach($data['localizations'] as $locale => $translation){
					$entity = new BundleEntity\FileLocalization();
					$lModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
					$response = $lModel->getLanguage($locale);
					if($response->error->exist){
						return $response;
					}
					$entity->setLanguage($response->result->set);
					unset($response);
					$entity->setFile($file);
					foreach($translation as $column => $value){
						$set = 'set'.$this->translateColumnName($column);
						switch($column){
							default:
								if(is_object($value) || is_array($value)){
									$value = json_encode($value);
								}
								$entity->$set($value);
								break;
						}
					}
					$this->em->persist($entity);
					$insertedItems[] = $entity;
					$countInserts++;
				}
			}
		}
		if($countInserts > 0){
			$this->em->flush();
			return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, microtime());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, microtime());
	}

	/**
	 * @param mixed $folder
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertFileUploadFolder($folder){
		return $this->insertFileUploadFolders(array($folder));
	}

	/**
	 * @param array $collection
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertFileUploadFolders(array $collection) {
		$timeStamp = microtime();

		$countInserts = 0;
		$insertedItems = [];
		foreach($collection as $data){
			if($data instanceof BundleEntity\FileUploadFolder){
				$entity = $data;
				$this->em->persist($entity);
				$insertedItems[] = $entity;
				$countInserts++;
			}
			else if(is_object($data)){
				$entity = new BundleEntity\FileUploadFolder();
				foreach($data as $column => $value){
					$set = 'set'.$this->translateColumnName($column);
					switch($column){
						case 'site':
							$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
							$response = $sModel->getSite($value);
							if(!$response->error->exist){
								$entity->$set($response->result->set);
							}
							unset($response, $sModel);
							break;
						default:
							$entity->$set($value);
							break;
					}
				}
				$this->em->persist($entity);
				$insertedItems[] = $entity;
				$countInserts++;
			}
		}
		if($countInserts > 0){
			$this->em->flush();
			return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, microtime());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, microtime());
	}

    /*
     *
     */
	public function listFiles(array $filter = null, array $sortOrder = null, array $limit = null){
		$timeStamp = microtime();
		$oStr = $wStr = $gStr = $fStr = '';

		$qStr = 'SELECT '.$this->entity['f']['alias'].', '.$this->entity['fl']['alias']
			.' FROM '.$this->entity['fl']['name'].' '.$this->entity['fl']['alias']
			.' JOIN '.$this->entity['fl']['alias'].'.file '.$this->entity['f']['alias'];

		if(!is_null($sortOrder)){
			foreach($sortOrder as $column => $direction){
				switch($column){
					case 'id':
					case 'name':
					case 'url_key':
					case 'width':
					case 'height':
					case 'size':
					case 'mime_type':
					case 'extension':
					case 'date_added':
					case 'date_updated':
					case 'date_removed':
						$column = $this->entity['f']['alias'].'.'.$column;
						break;
					case 'title':
						$column = $this->entity['fl']['alias'].'.'.$column;
						break;
					default:
						continue 2;
				}
				$oStr .= ' '.$column.' '.strtoupper($direction).', ';
			}
			if(!empty($oStr)){
				$oStr = rtrim($oStr, ', ');
				$oStr = ' ORDER BY '.$oStr.' ';
			}
		}

		if(!is_null($filter)){
			$fStr = $this->prepareWhere($filter);
			$wStr .= ' WHERE '.$fStr;
		}

		$qStr .= $wStr.$gStr.$oStr;
		$q = $this->em->createQuery($qStr);
		$q = $this->addLimit($q, $limit);

		$result = $q->getResult();

		$entities = [];
		foreach($result as $entry){
			$id = $entry->getFile()->getId();
			if(!isset($unique[$id])){
				$entities[] = $entry->getFile();
				$unique[$id] = '';
			}
		}
		$totalRows = count($entities);
		if ($totalRows < 1) {
			return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, microtime());
		}
		return new ModelResponse($entities, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime());
	}

	/**
	 * @param mixed $folder
	 * @param array|null $filter
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function listFilesInFolder($folder, array $filter = null, array $sortOrder = null, array $limit = null) {
		$timeStamp = microtime();
		$response = $this->getFileUploadFolder($folder);
		if($response->error->exist){
			return $response;
		}
		$folder = $response->result->set;
        $filter[] = array(
            'glue' => ' and',
            'condition' => array(
                'column' => $this->entity['f']['alias'] . '.file_upload_folder',
                'comparison' => '=',
                'value' => $folder->getId())
        );
        $response = $this->listFiles($filter, $sortOrder, $limit);

		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = microtime();

		return $response;
    }

	/**
	 * @param mixed $member
	 * @param mixed $folder
	 * @param array|null $filter
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listFilesOfMemberInFolder($member, $folder, array $filter = null, array $sortOrder = null, array $limit = null) {
		$timeStamp = microtime();
		$mModel = $this->kernel->getContainer()->get('membermanagement.model');
		$response = $this->getFileUploadFolder($folder);
		if($response->error->exist){
			return $response;
		}
		$folder = $response->result->set;
		$response = $mModel->getMember($member);
		if($response->error->exist){
			return $response;
		}
		$member = $response->result->set;
		unset($response);
		$filter[] = array(
			'glue' => ' and',
			'condition' => array(
				'column' => $this->entity['f']['alias'] . '.file_upload_folder',
				'comparison' => '=',
				'value' => $folder->getId())
		);
		$response = $this->listFiles($filter, $sortOrder, $limit);

		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = microtime();

		return $response;
	}
	/**
	 * @param mixed $site
	 * @param array|null $filter
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function listFilesOfSite($site, array $filter = null, array $sortOrder = null, array $limit = null) {
		$timeStamp = microtime();
		$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
		$response = $sModel->getSite($site);
		if($response->error->exist){
			return $response;
		}
		$site = $response->result->set;
		$filter[] = array(
			'glue' => ' and',
			'condition' => array(
				'column' => $this->entity['f']['alias'] . '.site',
				'comparison' => '=',
				'value' => $site->getId())
		);
		$response = $this->listFiles($filter, $sortOrder, $limit);

		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = microtime();

		return $response;
    }

	/**
	 * @param array|null $filter
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listFileUploadFolders(array $filter = null, array $sortOrder = null, array $limit = null) {
		$timeStamp = microtime();
		$oStr = $wStr = $gStr = $fStr = '';

		$qStr = 'SELECT '.$this->entity['fuf']['alias']
			.' FROM '.$this->entity['fuf']['name'].' '.$this->entity['fuf']['alias'];

		if (!is_null($sortOrder)) {
			foreach ($sortOrder as $column => $direction) {
				switch ($column) {
					case 'id':
					case 'name':
					case 'url_key':
					case 'path_absolute':
					case 'url':
					case 'type':
					case 'allowed_max_size':
					case 'allowed_min_size':
					case 'allowed_max_width':
					case 'allowed_min_width':
					case 'allowed_max_height':
					case 'allowed_min_height':
					case 'count_files':
					case 'site':
						$column = $this->entity['fuf']['alias'].'.'.$column;
						break;
				}
				$oStr .= ' '.$column.' '.strtoupper($direction).', ';
			}
			$oStr = rtrim($oStr, ', ');
			$oStr = ' ORDER BY '.$oStr.' ';
		}
		if (!is_null($filter)) {
			$fStr = $this->prepareWhere($filter);
			$wStr = ' WHERE '.$fStr;
		}

		$qStr .= $wStr.$gStr.$oStr;

		$q = $this->em->createQuery($qStr);
		$q = $this->addLimit($q, $limit);
		$result = $q->getResult();

		$totalRows = count($result);
		if ($totalRows < 1) {
			return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, microtime());
		}
		return new ModelResponse($result, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime());
	}

	/**
	 * @param string     $extension
	 * @param array|null $filter
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function listFilesWithExtension(string $extension, array $filter = null, array $sortOrder = null, array $limit = null) {

        $filter[] = array(
            'glue' => ' and',
            'condition' => array(
                'column' => $this->entity['f']['alias'] . '.extension',
                'comparison' => '=',
                'value' => $extension)
        );
        return $this->listFiles($filter, $sortOrder, $limit);
    }

	/**
	 * @param string     $type
	 * @param array|null $filter
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 * @throws \BiberLtd\Bundle\FileManagementBundle\Exception\InvalidFileTypeException
	 */
    public function listFilesWithType(string $type, array $filter = null, array $sortOrder = null, array $limit = null) {
		$timeStamp = microtime();
		$typeOpts = array('a', 'i', 'v', 'f', 'd', 'p', 's');
        if(!in_array($type, $typeOpts)){
	        throw new InvalidFileTypeException($type);
		}
        $filter[] = array(
            'glue' => ' and',
            'condition' => array(
                'column' => $this->entity['f']['alias'] . '.type',
                'comparison' => '=',
                'value' => $type)
        );
        $response = $this->listFiles($filter, $sortOrder, $limit);

		$response->stats->execution->start = $timeStamp;
		$response->stats->Execution->end = microtime();

		return $response;
    }

	/**
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function listDocumentFiles(array $sortOrder = null, array $limit = null) {
        return $this->listFilesWithType('d', $sortOrder, $limit);
    }

	/**
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listFlashFiles(array $sortOrder = null, array $limit = null) {
		return $this->listFilesWithType('f', $sortOrder, $limit);
	}

	/**
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listImages(array $sortOrder = null, array $limit = null) {
		return $this->listFilesWithType('i', $sortOrder, $limit);
	}

	/**
	 * @param int        $width
	 * @param int        $height
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function listImagesWithDimension(int $width, int $height, array $sortOrder = null, array $limit = null) {
		$timeStamp = microtime();

        $filter[] = array(
            'glue' => ' and',
            'condition' => array(
                0 => array(
                    'glue' => ' and',
                    'condition' => array(
                        'column' => $this->entity['f']['alias'] . '.width',
                        'comparison' => '=',
                        'value' => $width
                    )),
                1 => array(
                    'glue' => ' and',
                    'condition' => array(
                        'column' => $this->entity['f']['alias'] . '.height',
                        'comparison' => '=',
                        'value' => $height
                    )))
        );
        $response =  $this->listFiles($filter, $sortOrder, $limit);
		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = microtime();

		return $response;
    }

	/**
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listSoftwares(array $sortOrder = null, array $limit = null) {
		return $this->listFilesWithType('s', $sortOrder, $limit);
	}

	/**
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listVideos(array $sortOrder = null, array $limit = null) {
		return $this->listFilesWithType('v', $sortOrder, $limit);
	}

	/**
	 * @param mixed $file
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function updateFile($file) {
        return $this->updateFiles(array($file));
    }

	/**
	 * @param array $collection
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function updateFiles(array $collection){
		$timeStamp = microtime();
		$countUpdates = 0;
		$updatedItems = [];
		$localizations = [];
		foreach ($collection as $data) {
			if ($data instanceof BundleEntity\File) {
				$entity = $data;
				$this->em->persist($entity);
				$updatedItems[] = $entity;
				$countUpdates++;
			}
			else if (is_object($data)) {
				if(!property_exists($data, 'site')){
					$data->site = 1;
				}
				if(!property_exists($data, 'folder')){
					$data->folder = 1;
				}
				$response = $this->getFile($data->id);
				if ($response->error->exist) {
					return $this->createException('EntityDoesNotExist', 'File with id / username / email '.$data->id.' does not exist in database.', 'E:D:002');
				}
				$oldEntity = $response->result->set;
				foreach ($data as $column => $value) {
					$set = 'set' . $this->translateColumnName($column);
					switch ($column) {
						case 'local':
							foreach ($value as $langCode => $translation) {
								$localization = $oldEntity->getLocalization($langCode, true);
								$newLocalization = false;
								if (!$localization) {
									$newLocalization = true;
									$localization = new BundleEntity\FileLocalization();
									$mlsModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
									$response = $mlsModel->getLanguage($langCode);
									$localization->setLanguage($response->result->set);
									$localization->setFile($oldEntity);
								}
								foreach ($translation as $transCol => $transVal) {
									$transSet = 'set' . $this->translateColumnName($transCol);
									$localization->$transSet($transVal);
								}
								if ($newLocalization) {
									$this->em->persist($localization);
								}
								$localizations[] = $localization;
							}
							$oldEntity->setLocalizations($localizations);
							break;
						case 'site':
							$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
							$response = $sModel->getSite($value);
							if (!$response->error->exist) {
								$oldEntity->$set($response->result->set);
							} else {
								return $this->createException('EntityDoesNotExist', 'The site with the id / key / domain "'.$value.'" does not exist in database.', 'E:D:002');
							}
							unset($response, $sModel);
							break;
						case 'folder':
							$response = $this->getFileUploadFolder($value);
							if(!$response->error->exist){
								$entity->$set($response->result->set);
							}
							unset($response);
							break;
						case 'id':
							break;
						default:
							$oldEntity->$set($value);
							break;
					}
					if ($oldEntity->isModified()) {
						$this->em->persist($oldEntity);
						$countUpdates++;
						$updatedItems[] = $oldEntity;
					}
				}
			}
		}
		if($countUpdates > 0){
			$this->em->flush();
			return new ModelResponse($updatedItems, $countUpdates, 0, null, false, 'S:D:004', 'Selected entries have been successfully updated within database.', $timeStamp, microtime());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:004', 'One or more entities cannot be updated within database.', $timeStamp, microtime());
	}

	/**
	 * @param mixed $folder
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function updateFileUploadFolder($folder) {
        return $this->updateFileUploadFolders(array($folder));
    }

	/**
	 * @param array $collection
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function updateFileUploadFolders(array $collection){
		$timeStamp = microtime();
		$countUpdates = 0;
		$updatedItems = [];
		foreach($collection as $data){
			if($data instanceof BundleEntity\FileUploadFolder){
				$entity = $data;
				$this->em->persist($entity);
				$updatedItems[] = $entity;
				$countUpdates++;
			}
			else if(is_object($data)){
				if(!property_exists($data, 'id') || !is_numeric($data->id)){
					return $this->createException('InvalidParameterException', 'Parameter must be an object with the "id" parameter and id parameter must have an integer value.', 'E:S:003');
				}
				if(!property_exists($data, 'date_updated')){
					$data->date_updated = new \DateTime('now', new \DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));
				}
				if(!property_exists($data, 'date_added')){
					unset($data->date_added);
				}
				$response = $this->getFileUploadFolder($data->id);
				if($response->error->exist){
					return $this->createException('EntityDoesNotExist', 'File upload folder with id '.$data->id, 'E:D:002');
				}
				$oldEntity = $response->result->set;
				foreach($data as $column => $value){
					$set = 'set'.$this->translateColumnName($column);
					switch($column){
						case 'site':
							$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
							$response = $sModel->getSite($value);
							if(!$response->error->exist){
								$oldEntity->$set($response->result->set);
							}
							else{
								new CoreExceptions\EntityDoesNotExistException($this->kernel, $value);
							}
							unset($response, $sModel);
							break;
						case 'id':
							break;
						default:
							$oldEntity->$set($value);
							break;
					}
					if($oldEntity->isModified()){
						$this->em->persist($oldEntity);
						$countUpdates++;
						$updatedItems[] = $oldEntity;
					}
				}
			}
		}
		if($countUpdates > 0){
			$this->em->flush();
			return new ModelResponse($updatedItems, $countUpdates, 0, null, false, 'S:D:004', 'Selected entries have been successfully updated within database.', $timeStamp, microtime());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:004', 'One or more entities cannot be updated within database.', $timeStamp, microtime());
	}
}
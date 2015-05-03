<?php
/**
 * FileManagementModel
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\FileManagementBundle
 * @subpackage	Services
 * @name	    FileManagementModel
 *
 * @author		Can Berkol
 * @author      Said Imamoglu
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.5
 * @date        03.05.2015
 *
 */

namespace BiberLtd\Bundle\FileManagementBundle\Services;

/** Extends CoreModel */
use BiberLtd\Bundle\CoreBundle\CoreModel;
/** Entities to be used */
use BiberLtd\Bundle\FileManagementBundle\Entity as BundleEntity;
/** Helper Models */
use BiberLtd\Bundle\SiteManagementBundle\Services as SMMService;
/** Core Service */
use BiberLtd\Bundle\CoreBundle\Services as CoreServices;
use BiberLtd\Bundle\CoreBundle\Responses\ModelResponse;
use BiberLtd\Bundle\CoreBundle\Exceptions as CoreExceptions;

class FileManagementModel extends CoreModel {
    /**
     * @name            __construct()
     *                  Constructor.
     *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @since           1.0.0
     * @version         1.0.5
     *
     * @param           object          $kernel
     * @param           string          $db_connection  Database connection key as set in app/config.yml
     * @param           string          $orm            ORM that is used.
     */
    public function __construct($kernel, $db_connection = 'default', $orm = 'doctrine') {
        parent::__construct($kernel, $db_connection, $orm);

        $this->entity = array(
            'f' => array('name' => 'FileManagementBundle:File', 'alias' => 'f'),
            'fl' => array('name' => 'FileManagementBundle:FileLocalization', 'alias' => 'fl'),
            'fuf' => array('name' => 'FileManagementBundle:FileUploadFolder', 'alias' => 'fuf'),
        );
    }

    /**
     * @name            __destruct()
     *
     * @author          Said Imamoglu
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     */
    public function __destruct() {
        foreach ($this as $property => $value) {
            $this->$property = null;
        }
    }

	/**
	 * @name 			deleteFile()
	 *
	 * @since			1.0.0
	 * @version         1.0.5
	 * @author          Can Berkol
	 *
	 * @use             $this->deleteFiles()
	 *
	 * @param           mixed           $file
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function deleteFile($file){
		return $this->deleteFiles(array($file));
	}
	/**
	 * @name 			deleteFiles()
	 *
	 * @since			1.0.0
	 * @version         1.0.5
	 *
	 * @author          Can Berkol
	 *
	 * @use             $this->createException()
	 *
	 * @param           array           $collection
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function deleteFiles($collection) {
		$timeStamp = time();
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countDeleted = 0;
		foreach($collection as $entry){
			if($entry instanceof BundleEntity\File){
				$this->em->remove($entry);
				$countDeleted++;
			}
			else{
				$response = $this->getFile($entry);
				if(!$response->error->exists){
					$entry = $response->result->set;
					$this->em->remove($entry);
					$countDeleted++;
				}
			}
		}
		if($countDeleted < 0){
			return new ModelResponse(null, 0, 0, null, true, 'E:E:001', 'Unable to delete all or some of the selected entries.', $timeStamp, time());
		}
		$this->em->flush();

		return new ModelResponse(null, 0, 0, null, false, 'S:D:001', 'Selected entries have been successfully removed from database.', $timeStamp, time());
	}
	/**
	 * @name 			deleteFileUploadFolder()
	 *
	 * @since			1.0.0
	 * @version         1.0.5
	 * @author          Can Berkol
	 *
	 * @use             $this->deleteFieUploadFolders()
	 *
	 * @param           mixed           $folder
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function deleteFileUploadFolder($folder){
		return $this->deleteFileUploadFolders(array($folder));
	}
	/**
	 * @name 			deleteFileUploadFolders()
	 *
	 * @since			1.0.0
	 * @version         1.0.5
	 *
	 * @author          Can Berkol
	 *
	 * @use             $this->createException()
	 *
	 * @param           array           $collection
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function deleteFileUploadFolders($collection) {
		$timeStamp = time();
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countDeleted = 0;
		foreach($collection as $entry){
			if($entry instanceof BundleEntity\FileUploadFolder){
				$this->em->remove($entry);
				$countDeleted++;
			}
			else{
				$response = $this->getFileUploadFolder($entry);
				if(!$response->error->exists){
					$entry = $response->result->set;
					$this->em->remove($entry);
					$countDeleted++;
				}
			}
		}
		if($countDeleted < 0){
			return new ModelResponse(null, 0, 0, null, true, 'E:E:001', 'Unable to delete all or some of the selected entries.', $timeStamp, time());
		}
		$this->em->flush();

		return new ModelResponse(null, 0, 0, null, false, 'S:D:001', 'Selected entries have been successfully removed from database.', $timeStamp, time());
	}
	/**
	 * @name 			doesFileExist()
	 *
	 * @since			1.0.0
	 * @version         1.0.5
	 * @author          Can Berkol
	 *
	 * @use             $this->getSite()
	 *
	 * @param           mixed           $file
	 * @param           bool            $bypass         If set to true does not return response but only the result.
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function doesFileExist($file, $bypass = false) {
		$timeStamp = time();
		$exist = false;

		$response = $this->getFile($file);

		if ($response->error->exists) {
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
		return new ModelResponse(true, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
	/**
	 * @name 			doesFileUploadFolderExist()
	 *
	 * @since			1.0.0
	 * @version         1.0.5
	 * @author          Can Berkol
	 *
	 * @use             $this->getSite()
	 *
	 * @param           mixed           $folder
	 * @param           bool            $bypass         If set to true does not return response but only the result.
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function doesFileUploadFolderExist($folder, $bypass = false) {
		$timeStamp = time();
		$exist = false;

		$response = $this->getFileUploadFolder($folder);

		if ($response->error->exists) {
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
		return new ModelResponse(true, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
    /**
     * @name            getFile()
     *
     * @since			1.0.0
     * @version         1.0.5
	 *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->createException()
     *
     * @param           mixed           $file
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
	public function getFile($file) {
		$timeStamp = time();
		if($file instanceof BundleEntity\File){
			return new ModelResponse($file, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
		}
		$result = null;
		switch($file){
			case is_numeric($file):
				$result = $this->em->getRepository($this->entity['f']['name'])->findOneBy(array('id' => $file));
				break;
			case is_string($file):
				$result = $this->em->getRepository($this->entity['f']['name'])->findOneBy(array('url_key' => $file));
				break;
		}
		if(is_null($result)){
			return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, time());
		}

		return new ModelResponse($result, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
	/**
	 * @name            getFileUploadFolder()
	 *
	 * @since           1.0.0
	 * @version         1.0.5
	 *
	 * @author          Can Berkol
	 * @author          Said Imamoglu
	 *
	 * @use             $this->createException()
	 *
	 *
	 * @param           mixed   $folder
	 *
	 * @return          array   $response
	 *
	 */
	public function getFileUploadFolder($folder) {
		$timeStamp = time();
		if($folder instanceof BundleEntity\FileUploadFolder){
			return new ModelResponse($folder, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
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
			return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, time());
		}

		return new ModelResponse($result, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
    /**
     * @name 		    insertFile()
     *
     * @since		    1.0.1
     * @version         1.0.5
     * @author          Said Imamoglu
     * @author          Can Berkol
     *
     * @use             $this->insertFiles()
     *
     * @param           array           $file        Collection of entities or post data.
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function insertFile($file) {
        return $this->insertFiles(array($file));
    }
	/**
	 * @name            insertFiles()
	 *
	 * @since           1.0.1
	 * @version         1.0.5
	 * @author          Said Imamoglu
	 * @author          Can Berkol
	 *
	 * @use             $this->createException()
	 *
	 * @param           array           $collection
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertFiles($collection)	{
		$timeStamp = time();
		/** Parameter must be an array */
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countInserts = 0;
		$countLocalizations = 0;
		$insertedItems = array();
		$localizations = array();
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
			$response = $this->insertMemberLocalizations($localizations);
		}
		if($countInserts > 0){
			$this->em->flush();
			return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, time());
	}
    /**
     * @name            insertFileLocalizations()
     *
     * @since           1.0.4
     * @version         1.0.5
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection Collection of entities or post data.
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
	public function insertFileLocalizations($collection) {
		$timeStamp = time();
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countInserts = 0;
		$insertedItems = array();
		foreach($collection as $data){
			if($data instanceof BundleEntity\FileLocalization){
				$entity = $data;
				$this->em->persist($entity);
				$insertedItems[] = $entity;
				$countInserts++;
			}
			else if(is_object($data)){
				$entity = new BundleEntity\FileLocalization();
				foreach($data as $column => $value){
					$set = 'set'.$this->translateColumnName($column);
					switch($column){
						case 'language':
							$lModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
							$response = $lModel->getLanguage($value);
							if(!$response->error->exists){
								$entity->$set($response->result->set);
							}
							unset($response, $lModel);
							break;
						case 'file':
							$response = $this->getFile($value);
							if(!$response->error->exists){
								$entity->$set($response->result->set);
							}
							unset($response, $lModel);
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
			return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, time());
	}
	/**
	 * @name 			insertFileUploadFolder()
	 *
	 * @since			1.0.0
	 * @version         1.0.5
	 * @author          Can Berkol
	 *
	 * @use             $this->insertFileUploadFolders()
	 *
	 * @param           mixed           $folder
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertFileUploadFolder($folder){
		return $this->insertFileUploadFolders(array($folder));
	}
	/**
	 * @name 			insertFileUploadFolders()
	 *
	 * @since			1.0.0
	 * @version         1.0.5
	 * @author          Can Berkol
	 *
	 * @use             $this->createException()
	 *
	 * @param           array           $collection      Collection of Site entities or array of site detais array.
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertFileUploadFolders($collection) {
		$timeStamp = time();
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countInserts = 0;
		$insertedItems = array();
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
							if(!$response->error->exists){
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
			return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, time());
	}

    /**
     * @name            listFiles()
     *
     * @since		    1.0.0
     * @version         1.0.5
     *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->createException()
     *
     * @param           mixed           $filter
     * @param           array           $sortOrder
     * @param           array           $limit
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
	public function listFiles($filter = null, $sortOrder = null, $limit = null){
		$timeStamp = time();
		if(!is_array($sortOrder) && !is_null($sortOrder)){
			return $this->createException('InvalidSortOrderException', '$sortOrder must be an array with key => value pairs where value can only be "asc" or "desc".', 'E:S:002');
		}
		$oStr = $wStr = $gStr = $fStr = '';

		$qStr = 'SELECT '.$this->entity['f']['alias'].', '.$this->entity['f']['alias']
			.' FROM '.$this->entity['fl']['name'].' '.$this->entity['fl']['alias']
			.' JOIN '.$this->entity['fl']['alias'].'.member '.$this->entity['f']['alias'];

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
				}
				$oStr .= ' '.$column.' '.strtoupper($direction).', ';
			}
			$oStr = rtrim($oStr, ', ');
			$oStr = ' ORDER BY '.$oStr.' ';
		}

		if(!is_null($filter)){
			$fStr = $this->prepareWhere($filter);
			$wStr .= ' WHERE '.$fStr;
		}

		$qStr .= $wStr.$gStr.$oStr;
		$q = $this->em->createQuery($qStr);
		$q = $this->addLimit($q, $limit);

		$result = $q->getResult();

		$entities = array();
		foreach($result as $entry){
			$id = $entry->getFile()->getId();
			if(!isset($unique[$id])){
				$entities[] = $entry->getFile();
			}
		}
		$totalRows = count($entities);
		if ($totalRows < 1) {
			return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, time());
		}
		return new ModelResponse($entities, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}

    /**
     * @name            listFilesInFolder()
     * 
     * @since           1.0.0
     * @version         1.0.5
	 *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->listFiles()
     * 
     * @param           mixed		$folder
     * @param           array 		$filter
	 * @param			array 		$sortOrder
	 * @param			array		$limit
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     * 
     */

    public function listFilesInFolder($folder, $filter = null, $sortOrder = null, $limit = null) {
		$timeStamp = time();
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
		$response->stats->execution->end = time();

		return $response;
    }

    /**
     * @name            listFilesOfSite()
     *                  Lists files of a given site
     * 
     * @since           1.0.0
     * @version         1.0.5
	 *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->listFiles()
     * 
     * @param           mixed		$site
	 * @param			array		$filter
	 * @param			array		$sortOrder
	 * @param			array		$limit
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     * 
     */
    public function listFilesOfSite($site, $filter = null, $sortOrder = null, $limit = null) {
		$timeStamp = time();
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
		$response->stats->execution->end = time();

		return $response;
    }
	/**
	 * @name            listFileUploadFolders()
	 *
	 * @since           1.0.0
	 * @version         1.0.5
	 *
	 * @author          Can Berkol
	 * @author          Said Imamoglu
	 *
	 * @use             $this->createException()
	 *
	 * @param           array   $filter
	 * @param			array	$sortOrder
	 * @param			array	$limit
	 *
	 * @return          array   $response
	 *
	 */
	public function listFileUploadFolders($filter = null, $sortOrder = null, $limit = null) {
		$timeStamp = time();
		if (!is_array($sortOrder) && !is_null($sortOrder)) {
			return $this->createException('InvalidSortOrderException', '$sortOrder must be an array with key => value pairs where value can only be "asc" or "desc".', 'E:S:002');
		}

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
			return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, time());
		}
		return new ModelResponse($result, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
    /**
     * @name            listFilesWithExtension()
     * 
     * @since           1.0.0
     * @version         1.0.5
	 *
     * @author          Can Berkol
     * @author          Said Imamoğlu
     *
     * @use             $this->listFiles()
     * 
     * @param           string		$extension
	 * @param			array		$filter
	 * @param			array		$sortOrder
	 * @param			array		$limit
     * 
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     * 
     */
    public function listFilesWithExtension($extension, $filter = null, $sortOrder = null, $limit = null) {
        if (!is_string($extension)) {
			return $this->createException('InvalidParameterValueException', 'Extension must be a string.', 'E:S:007');
        }
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
     * @name            listFilesWithType()
     * 
     * @since           1.0.0
     * @version         1.0.5
	 *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->listFiles()
     * 
     * @param           string		$type
	 * @param			array		$filter
	 * @param			array		$sortOrder
	 * @param			array		$limit
     * 
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */

    public function listFilesWithType($type, $filter = null, $sortOrder = null, $limit = null) {
		$timeStamp = time();
		$typeOpts = array('a', 'i', 'v', 'f', 'd', 'p', 's');
        if(!in_array($type, $typeOpts)){
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Type must be one of the following: '.implode(', ', $typeOpts).'.', 'E:S:004');
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
		$response->stats->Execution->end = time();

		return $response;
    }

    /**
     * @name            listDocuments()
     * 
     * @since           1.0.0
     * @version         1.0.5
	 *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->listFilesWithType()
     *
	 * @param			array		$sortOrder
	 * @param			array		$limit
     * 
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     * 
     */
    public function listDocumentFiles($sortOrder = null, $limit = null) {
        return $this->listFilesWithType('d', $sortOrder, $limit);
    }

	/**
	 * @name            listFlashFiles()
	 *
	 * @since           1.0.0
	 * @version         1.0.5
	 *
	 * @author          Can Berkol
	 * @author          Said Imamoglu
	 *
	 * @use             $this->listFilesWithType()
	 *
	 * @param			array		$sortOrder
	 * @param			array		$limit
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 *
	 */
	public function listFlashFiles($sortOrder = null, $limit = null) {
		return $this->listFilesWithType('f', $sortOrder, $limit);
	}

	/**
	 * @name            listImages()
	 *
	 * @since           1.0.0
	 * @version         1.0.5
	 *
	 * @author          Can Berkol
	 * @author          Said Imamoglu
	 *
	 * @use             $this->listFilesWithType()
	 *
	 * @param			array		$sortOrder
	 * @param			array		$limit
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 *
	 */
	public function listImages($sortOrder = null, $limit = null) {
		return $this->listFilesWithType('i', $sortOrder, $limit);
	}

    /**
     * @name            listImagesWithDimension()
     * 
     * @since           1.0.0
     * @version         1.0.5
	 *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->createException()
     * @use             $this->listFiles()
     * 
     * @param           integer 	$width
     * @param           integer 	$height
     * @param           array 		$sortOrder
     * @param           array 		$limit
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     * 
     */

    public function listImagesWithDimension($width, $height, $sortOrder = null, $limit = null) {
		$timeStamp = time();
        if (!is_integer($width) || !is_integer($height)) {
            return $this->createException('InvalidParameterValueException', '$width and $height parameters must be integers.', 'E:S:008');
        }

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
		$response->stats->execution->end = time();

		return $response;
    }

	/**
	 * @name            listSoftwares()
	 *
	 * @since           1.0.0
	 * @version         1.0.5
	 *
	 * @author          Can Berkol
	 * @author          Said Imamoglu
	 *
	 * @use             $this->listFilesWithType()
	 *
	 * @param			array		$sortOrder
	 * @param			array		$limit
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 *
	 */
	public function listSoftwares($sortOrder = null, $limit = null) {
		return $this->listFilesWithType('s', $sortOrder, $limit);
	}

	/**
	 * @name            listVideos()
	 *
	 * @since           1.0.0
	 * @version         1.0.5
	 *
	 * @author          Can Berkol
	 * @author          Said Imamoglu
	 *
	 * @use             $this->listFilesWithType()
	 *
	 * @param			array		$sortOrder
	 * @param			array		$limit
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 *
	 */
	public function listVideos($sortOrder = null, $limit = null) {
		return $this->listFilesWithType('v', $sortOrder, $limit);
	}
    /**
     * @name            updateFile()
     * 
     * @since           1.0.0
     * @version         1.0.5
	 *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->resetResponse()
     * @use             $this->updateFiles()
     * 
     * @param           mixed   $file     entity, id
     * 
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     * 
     */
    public function updateFile($file) {
        return $this->updateFiles(array($file));
    }

    /**
     * @name            updateFiles()
     * 
     * @since           1.0.0
     * @version         1.0.5
     *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->createException()
     *
     * @param           array   $collection
     * 
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
	public function updateFiles($collection){
		$timeStamp = time();
		/** Parameter must be an array */
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countUpdates = 0;
		$updatedItems = array();
		$localizations = array();
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
			return new ModelResponse($updatedItems, $countUpdates, 0, null, false, 'S:D:004', 'Selected entries have been successfully updated within database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:004', 'One or more entities cannot be updated within database.', $timeStamp, time());
	}


    /**
     * @name            updateFileUploadFolder()
     * 
     * @since           1.0.0
     * @version         1.0.5
	 *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->updateFileUploadFolders()
     * 
     * @param           mixed   $folder
     * 
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     * 
     */

    public function updateFileUploadFolder($folder) {
        return $this->updateFileUploadFolders(array($folder));
    }

    /**
     * @name            updateFileUploadFolders()
     * 
     * @since           1.0.0
     * @version         1.0.5
	 *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->createException()
	 *
     * @param           mixed   $collection
     * 
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     * 
     */
	public function updateFileUploadFolders($collection){
		$timeStamp = time();
		/** Parameter must be an array */
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countUpdates = 0;
		$updatedItems = array();
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
			return new ModelResponse($updatedItems, $countUpdates, 0, null, false, 'S:D:004', 'Selected entries have been successfully updated within database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:004', 'One or more entities cannot be updated within database.', $timeStamp, time());
	}
}
/**
 * Change Log
 * **************************************
 * v1.0.5                      03.05.2015
 * Can Berkol
 * **************************************
 * CR :: Made compatible with CoreBundle v3.3.
 *
 * **************************************
 * v1.0.4                      Can Berkol
 * 17.07.2014
 * **************************************
 * A insertFileLocalizations()
 * U listFiles()
 *
 * **************************************
 * v1.0.2                      Can Berkol
 * 22.03.2014
 * **************************************
 * U updateFile()
 * U updateFiles()
 *
 * **************************************
 * v1.0.0                      Said Imamoglu
 * 15.11.2013
 * **************************************
 * A deleteFile()
 * A deleteFiles()
 * A doesFileExist()
 * A getFile()
 * A insertFile()
 * A insertFiles()
 * A listFiles
 * A listFilesInFolders()
 * A listFilesOfSite()
 * A listFilesWithExtension()
 * A listFilesWithType()
 * A listDocumentFiles()
 * A listFlashFiles()
 * A listImageFiles()
 * A listImageFilesWithDimension()
 * A listSoftwareFiles()
 * A listVideoFiles()
 * A updateFile()
 * A updateFiles()
 * A deleteFileUploadFolder()
 * A doesFileUploadFolderExist()
 * A getFileUploadFolder()
 * A insertFileUploadFolder()
 * A insertFileUploadFolders()
 * A listFileUploadFolders()
 * A listFileUploadFoldersWithLessFilesThan()
 * A listFileUploadFoldersWithMoreFilesThan()
 * A updateFileUploadFolder()
 * A updateFileUploadFolders()
 * 
 * 
 */
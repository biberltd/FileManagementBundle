<?php

/**
 * FileManagementModel Class
 *
 * This class acts as a database proxy model for FileManagementModelBundle functionalities.
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
 * @version     1.0.4
 * @date        17.07.2014
 *
 * =============================================================================================================
 * !! INSTRUCTIONS ON IMPORTANT ASPECTS OF MODEL METHODS !!!
 *
 * Each model function must return a $response ARRAY.
 * The array must contain the following keys and corresponding values.
 *
 * $response = array(
 *              'result'    =>   An array that contains the following keys:
 *                               'set'         Actual result set returned from ORM or null
 *                               'total_rows'  0 or number of total rows
 *                               'last_insert_id' The id of the item that is added last (if insert action)
 *              'error'     =>   true if there is an error; false if there is none.
 *              'code'      =>   null or a semantic and short English string that defines the error concanated
 *                               with dots, prefixed with err and the initials of the name of model class.
 *                               EXAMPLE: err.amm.action.not.found success messages have a prefix called scc..
 *
 *                               NOTE: DO NOT FORGET TO ADD AN ENTRY FOR ERROR CODE IN BUNDLE'S
 *                               RESOURCES/TRANSLATIONS FOLDER FOR EACH LANGUAGE.
 * =============================================================================================================   
 *
 */

namespace BiberLtd\Bundle\FileManagementBundle\Services;

/** Extends CoreModel */
use BiberLtd\Core\CoreModel;
/** Entities to be used */
use BiberLtd\Bundle\FileManagementBundle\Entity as BundleEntity;
/** Helper Models */
use BiberLtd\Bundle\SiteManagementBundle\Services as SMMService;
/** Core Service */
use BiberLtd\Core\Services as CoreServices;
use BiberLtd\Core\Exceptions as CoreExceptions;

class FileManagementModel extends CoreModel {
    /**
     * @name            __construct()
     *                  Constructor.
     *
     * @author          Said Imamoglu
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           object          $kernel
     * @param           string          $db_connection  Database connection key as set in app/config.yml
     * @param           string          $orm            ORM that is used.
     */

    /** @var $by_opitons handles by options */
    public $by_opts = array('entity', 'id', 'code', 'url_key', 'post', 'name');

    /* @var $type must be [i=>image,s=>software,v=>video,f=>flash,d=>document,p=>package] */
    public $type_opts = array('i', 'a', 'v', 'f', 'd', 'p', 's');

    public function __construct($kernel, $db_connection = 'default', $orm = 'doctrine') {
        parent::__construct($kernel, $db_connection, $orm);

        /**
         * Register entity names for easy reference.
         */
        $this->entity = array(
            'file' => array('name' => 'FileManagementBundle:File', 'alias' => 'f'),
            'file_localization' => array('name' => 'FileManagementBundle:FileLocalization', 'alias' => 'fl'),
            'file_upload_folder' => array('name' => 'FileManagementBundle:FileUploadFolder', 'alias' => 'fuf'),
        );
    }

    /**
     * @name            __destruct()
     *                  Destructor.
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
     * @name 	        deleteFile()
     *                  Delete files with a given id or entity.
     *
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     *
     * @use             $this->deleteFiles()
     *
     * @param           array           $data           Collection consists one of the following: 'entity' or entity 'id'
     *                                                  Contains an array with two keys: file, and sortorder
     *
     * @return          array           $response
     */
    public function deleteFile($data) {
        return $this->deleteFiles(array($data));
    }

    /**
     * @name 	        deleteFiles()
     *                  Delete files with a given id or entity.
     *
     * @since           1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->deleteFiles()
     *
     * @param           array           $collection           Collection consists one of the following: 'entity' or entity 'id'
     *                                                        Contains an array with two keys: file, and sortorder
     *
     * @return          array           $response
     */
    public function deleteFiles($collection) {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', '', 'err.invalid.parameter.value');
        }
        /** If COLLECTION is ENTITYs then USE ENTITY MANAGER */
        $removeCount = 0;
        foreach($collection as $entity){
            if(!$entity instanceof BundleEntity\File){
                if(is_numeric($entity)){
                    $response = $this->getFile($entity, 'id');
                    if($response['error']){
                        return $this->createException('EntityDoesNotExist', 'File', 'err.invalid.entity.file');
                    }
                    $entity = $response['result']['set'];
                    unset($response);
                }
                else{
                    return $this->createException('InvalidParameterException', 'collection', 'err.invalid.parameter.exception');
                }
            }
            $this->em->remove($entity);
            $removeCount++;
        }
        if ($removeCount > 0) {
            $this->em->flush();
        };
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => null,
                    'total_rows' => $removeCount,
                    'last_insert_id' => null,
                ),
                'error' => false,
                'code' => 'scc.db.deleted',
            );
        return $this->response;
    }

    /**
     * @name 	doesFileExist()
     *  	Checks if record exist in db.
     *
     * @since		1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     *
     * @use             $this->resetResponse()
     * @use             $this->getFile()
     *
     * @param           array           $file   File collection of entities or post data.
     * @param           string          $by     Entity, post
     *
     * @return          array           $response
     */
    public function doesFileExist($file, $by = 'entity') {
        $this->resetResponse();
        $exist = false;
        $code = 'err.db.record.notfound';
        $error = false;

        $response = $this->getFile($file, $by);

        if (!$response['error']) {
            $exist = true;
            $code = 'scc.db.record.found';
        } else {
            $error = true;
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $exist,
                'total_rows' => $response['result']['total_rows'],
                'last_insert_id' => null,
            ),
            'error' => $error,
            'code' => $code,
        );
        return $this->response;
    }

    /**
     * @name            getFile()
     *  		Returns details of a file.
     *
     * @since		1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     *
     * @use             $this->createException()
     * @use             $this->listFiles()
     *
     * @param           mixed           $file               id
     * @param           string          $by                 entity, id
     *
     * @return          mixed           $response
     */
    public function getFile($file, $by = 'id') {
        $this->resetResponse();
        if (!is_object($file) && !is_numeric($file) && !is_string($file)) {
            return $this->createException('InvalidParameterException', 'object,numeric or string', 'err.invalid.parameter.file');
        }
        if ($by == 'entity') {
            if (is_object($file)) {
                if (!$file instanceof BundleEntity\File) {
                    return $this->createException('InvalidEntityException', 'BundleEntity\File', 'err.invalid.parameter.file');
                }
                /**
                 * Prepare and Return Response
                 */
                $this->response = array(
	                'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => $file,
                        'total_rows' => 1,
                        'last_insert_id' => null,
                    ),
                    'error' => false,
                    'code' => 'scc.entity.found'
                );
            } else {
                return $this->createException('InvalidParameterException', 'object,numeric or string', 'err.invalid.parameter.file');
            }
        } elseif ($by == 'id') {
            $filter[] = array(
                'glue' => '',
                'condition' => array('column' => $this->entity['file']['alias'] . '.' . $by, 'comparison' => '=', 'value' => $file)
            );
            $response = $this->listFiles($filter, null);
            if ($response['error']) {
                return $response;
            }
            $collection = $response['result']['set'];

            /**
             * Prepare and Return Response
             */
            $this->response = array(
	        'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => $collection[0],
                    'total_rows' => count($collection),
                    'last_insert_id' => null,
                ),
                'error' => false,
                'code' => 'scc.entity.found',
            );
        }

        return $this->response;
    }

    /**
     * @name 		    insertFile()
     *  		        Inserts one or more products into database.
     *
     * @since		    1.0.1
     * @version         1.0.2
     * @author          Said Imamoglu
     * @author          Can Berkol
     *
     * @use             $this->insertFiles()
     *
     * @param           array           $file        Collection of entities or post data.
     *
     * @return          array           $response
     */
    public function insertFile($file) {
        return $this->insertFiles(array($file));
    }
    /**
     * @name            insertFileLocalizations ()
     *                  Inserts one or more product localizations into database.
     *
     * @since           1.0.4
     * @version         1.0.4
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection Collection of entities or post data.
     *
     * @return          array           $response
     */
    public function insertFileLocalizations($collection){
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameter', 'Array', 'err.invalid.parameter.collection');
        }
        $countInserts = 0;
        $insertedItems = array();
        foreach ($collection as $item) {
            if ($item instanceof BundleEntity\FileLocalization) {
                $entity = $item;
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            } else {
                foreach ($item['localizations'] as $language => $data) {
                    $entity = new BundleEntity\FileLocalization;
                    $entity->setFile($item['entity']);
                    $mlsModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
                    $response = $mlsModel->getLanguage($language, 'iso_code');
                    if (!$response['error']) {
                        $entity->setLanguage($response['result']['set']);
                    } else {
                        break 1;
                    }
                    foreach ($data as $column => $value) {
                        $set = 'set' . $this->translateColumnName($column);
                        $entity->$set($value);
                    }
                    $this->em->persist($entity);
                }
                $insertedItems[] = $entity;
                $countInserts++;
            }
        }
        if ($countInserts > 0) {
            $this->em->flush();
        }
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $insertedItems,
                'total_rows' => $countInserts,
                'last_insert_id' => -1,
            ),
            'error' => false,
            'code' => 'scc.db.insert.done',
        );
        return $this->response;
    }
    /**
     * @name            insertFiles()
     *  		        Inserts one or more files into database.
     *
     * @since           1.0.1
     * @version         1.0.2
     * @author          Said Imamoglu
     * @author          Can Berkol
     *
     * @use             $this->createException()
     * @use             $this->doesProductExist()
     * @use             BiberLtd\Bundle\FileManagementBundle\Entity\File()
     * @use             BiberLtd\Bundle\FileManagementBundle\Entity\FileUploadFolder()
     *
     * @throws          InvalidParameterException
     * @throws          InvalidMethodException
     *
     * @param           array           $collection        Collection of entities or post data.
     *
     * @return          array           $response
     */
    public function insertFiles($collection) {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $countInserts = 0;
        $countLocalizations = 0;
        $insertedItems = array();
        foreach($collection as $data){
            if($data instanceof BundleEntity\File){
                $entity = $data;
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            }
            else if(is_object($data) || is_array($data)){
                if(is_array($data)){
                    $obj = new \stdClass();
                    foreach($data as $key => $value){
                        $obj->$key = $value;
                    }
                    $data = $obj;
                    unset($obj);
                }
                $localizations = array();
                $entity = new BundleEntity\File;
                if(!property_exists($data, 'site')){
                    $data->site = 1;
                }
                if(!property_exists($data, 'folder')){
                    $data->folder = 1;
                }
                $localeSet = false;
                foreach($data as $column => $value){
                    $set = 'set'.$this->translateColumnName($column);
                    switch($column){
                        case 'local':
                            $localizations[$countInserts]['localizations'] = $value;
                            $localeSet = true;
                            $countLocalizations++;
                            break;
                        case 'site':
                            $sModel = $this->kernel->getContainer()->get('sitemanagement.model');
                            $response = $sModel->getSite($value, 'id');
                            if(!$response['error']){
                                $entity->$set($response['result']['set']);
                            }
                            else{
                                new CoreExceptions\SiteDoesNotExistException($this->kernel, $value);
                            }
                            unset($response, $sModel);
                            break;
                        case 'folder':
                            $response = $this->getFileUploadFolder($value, 'id');
                            if(!$response['error']){
                                $entity->$set($response['result']['set']);
                            }
                            else{
                                new CoreExceptions\EntityDoesNotExistException($this->kernel, $value);
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
            else{
                new CoreExceptions\InvalidDataException($this->kernel);
            }
        }
        if($countInserts > 0){
            $this->em->flush();
        }
        /** Now handle localizations */
        if ($countInserts > 0 && $countLocalizations > 0) {
            $this->insertFileLocalizations($localizations);
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $insertedItems,
                'total_rows' => $countInserts,
                'last_insert_id' => $entity->getId(),
            ),
            'error' => false,
            'code' => 'scc.db.insert.done',
        );
        return $this->response;
    }

    /**
     * @name            listFiles()
     *                  List files of a given collection.
     *
     * @since		    1.0.0
     * @version         1.0.4
     *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->resetResponse()
     * @use             $this->createException()
     * @use             $this->prepare_where()
     * @use             $this->createQuery()
     * @use             $this->getResult()
     * 
     * @throws          InvalidSortOrderException
     * @throws          InvalidLimitException
     * 
     *
     * @param           mixed           $filter                Multi dimensional array
     * @param           array           $sortorder              Array
     *                                                              'column'    => 'asc|desc'
     * @param           array           $limit
     *                                      start
     *                                      count
     * @param           string           $query_str             If a custom query string needs to be defined.
     *
     * @return          array           $response
     */
    public function listFiles($filter = null, $sortorder = null, $limit = null, $query_str = null) {
        $this->resetResponse();
        if (!is_array($sortorder) && !is_null($sortorder)) {
            return $this->createException('InvalidSortOrderException', '', 'err.invalid.parameter.sortorder');
        }

        /**
         * Add filter check to below to set join_needed to true
         */
        $order_str = '';
        $where_str = '';
        $group_str = '';
        $filter_str = '';

        /**
         * Start creating the query
         *
         * Note that if no custom select query is provided we will use the below query as a start
         */

        /**
         * Prepare ORDER BY section of query
         */
        $localizedQuery = false;
        if (!is_null($sortorder)) {
            foreach ($sortorder as $column => $direction) {
                switch ($column) {
                    case 'id':
                    case 'name':
                    case 'url_key':
                    case 'source_original':
                    case 'source_preview':
                    case 'type':
                    case 'width':
                    case 'height':
                    case 'size':
                    case 'folder':
                    case 'mime_type':
                    case 'extension':
                    case 'site':
                        $column = $this->entity['file']['alias'].'.'.$column;
                        break;
                    case 'title':
                    case 'description':
                        $localizedQuery = true;
                        $column = $this->entity['file_localization']['alias'].'.'.$column;
                        break;
                }
                $order_str .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY ' . $order_str . ' ';
        }
        if($localizedQuery){
            if (is_null($query_str)) {
                $query_str = 'SELECT '.$this->entity['file_localization']['alias']
                    .' FROM '.$this->entity['file_localization']['name'].' '.$this->entity['file_localization']['alias']
                    .' JOIN '.$this->entity['file_localization']['alias'].'.file '.$this->entity['file']['alias'];
            }
        }
        else{
            if (is_null($query_str)) {
                $query_str = 'SELECT '.$this->entity['file']['alias']
                    .' FROM '.$this->entity['file']['name'].' '.$this->entity['file']['alias'];
            }
        }

        /**
         * Prepare WHERE section of query
         */
        if (!is_null($filter)) {
            $filter_str = $this->prepare_where($filter);
            $where_str = ' WHERE ' . $filter_str;
        }

        $query_str .= $where_str . $group_str . $order_str;

        $query = $this->em->createQuery($query_str);

        /**
         * Prepare LIMIT section of query
         */

        if (!is_null($limit) && is_numeric($limit)) {
            if (isset($limit['start']) && isset($limit['count'])) {
                $query = $this->addLimit($query, $limit);
            } else {
                $this->createException('InvalidLimitException', '', 'err.invalid.limit');
            }
        }
        /**
         * Prepare and Return Response
         */
        $result = $query->getResult();
        $entries = array();
        $unique = array();
        if($localizedQuery){
            foreach ($result as $entry) {
                $id = $entry->getFile()->getId();
                if (!isset($unique[$id])) {
                    $entries[] = $entry->getFile();
                    $unique[$id] = $entry->getFile();
                }
            }
        }
        else{
            $entries = $result;
        }
        $total_rows = count($entries);
        if ($total_rows < 1) {
            $this->response['error'] = true;
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $entries,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );

        return $this->response;
    }

    /**
     * @name            listFilesInFolder()
     *                  Lists files of a given folder
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->listFiles()
     * 
     * @param           integer $folder         Folder ID
     * 
     * @return          array   $response
     * 
     */

    public function listFilesInFolder($folder, $filter = null, $sortorder = null, $limit = null, $query_str = null) {
        $filter[] = array(
            'glue' => ' and',
            'condition' => array(
                'column' => $this->entity['file']['alias'] . '.file_upload_folder',
                'comparison' => '=',
                'value' => $folder)
        );
        return $this->listFiles($filter, $sortorder, $limit, $query_str);
    }

    /**
     * @name            listFilesOfSite()
     *                  Lists files of a given site
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->listFiles()
     * 
     * @param           integer   $folder     Site ID
     * 
     * @return          array   $response
     * 
     */

    public function listFilesOfSite($site, $filter = null, $sortorder = null, $limit = null, $query_str = null) {
        $filter[] = array(
            'glue' => ' and',
            'condition' => array(
                'column' => $this->entity['file']['alias'] . '.site',
                'comparison' => '=',
                'value' => $site)
        );
        return $this->listFiles($filter, $sortorder, $limit, $query_str);
    }

    /**
     * @name            listFilesWithExtension()
     *                  Lists files of a given extension
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->listFiles()
     * 
     * @param           string  $extension     Extension name (jpg,png etc..)
     * 
     * @return          array   $response
     * 
     */

    public function listFilesWithExtension($extension, $filter = null, $sortorder = null, $limit = null, $query_str = null) {
        if (!is_string($extension)) {
            return $this->createException('InvalidParameterException', 'string', 'err.invalid.parameter.extension');
        }

        $filter[] = array(
            'glue' => ' and',
            'condition' => array(
                'column' => $this->entity['file']['alias'] . '.extension',
                'comparison' => '=',
                'value' => $extension)
        );
        return $this->listFiles($filter, $sortorder, $limit, $query_str);
    }

    /**
     * @name            listFilesWithType()
     *                  Lists files of a given type
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->type_opts
     * @use             $this->listFiles()
     * 
     * @param           char    $type        Code of type (image use i,for audio use a)
     * 
     * @return          array   $response
     * 
     */

    public function listFilesWithType($type, $filter = null, $sortorder = null, $limit = null, $query_str = null) {
        if (strlen($type) > 1 || is_integer($type) || !in_array($type, $this->type_opts)) {
            return $this->createException('InvalidParameterException', 'string', 'err.invalid.parameter.extension');
        }

        $filter[] = array(
            'glue' => ' and',
            'condition' => array(
                'column' => $this->entity['file']['alias'] . '.type',
                'comparison' => '=',
                'value' => $type)
        );
        return $this->listFiles($filter, $sortorder, $limit, $query_str);
    }

    /**
     * @name            listDocumentFiles()
     *                  Lists document files
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->listFilesWithType()
     * 
     * @param           mixed   $type     Code of type 
     * 
     * @return          array   $response
     * 
     */

    public function listDocumentFiles($type = 'd', $filter = null, $sortorder = null, $limit = null, $query_str = null) {
        return $this->listFilesWithType($type, $sortorder, $limit, $query_str);
    }

    /**
     * @name            listFlashFiles()
     *                  Lists flash files 
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->listFilesWithType()
     * 
     * @param           mixed   $type     Code of type
     * 
     * @return          array   $response
     * 
     */

    public function listFlashFiles($type = 'f', $filter = null, $sortorder = null, $limit = null, $query_str = null) {
        return $this->listFilesWithType($type, $sortorder, $limit, $query_str);
    }

    /**
     * @name            listImageFiles()
     *                  Lists Image files 
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->listFilesWithType()
     * 
     * @param           mixed   $type     Code of type
     * 
     * @return          array   $response
     * 
     */

    public function listImageFiles($type = 'i', $filter = null, $sortorder = null, $limit = null, $query_str = null) {
        return $this->listFilesWithType($type, $sortorder, $limit, $query_str);
    }

    /**
     * @name            listImageFilesWithDimension()
     *                  Lists files of a given width and height
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->createException()
     * @use             $this->listFiles()
     * 
     * @throws          InvalidParameterException
     * 
     * @param           integer $width      Width of file
     * @param           integer $height     Height of file
     * 
     * @return          array   $response
     * 
     */

    public function listImageFilesWithDimension($width, $height, $sortorder = null, $limit = null, $query_str = null) {
        if (!is_integer($width) || !is_integer($height)) {
            return $this->createException('InvalidParameterException', 'expected integer dimensions', 'err.invalid.parameter.dimension');
        }

        $filter[] = array(
            'glue' => ' and',
            'condition' => array(
                0 => array(
                    'glue' => ' and',
                    'condition' => array(
                        'column' => $this->entity['file']['alias'] . '.width',
                        'comparison' => '=',
                        'value' => $width
                    )),
                1 => array(
                    'glue' => ' and',
                    'condition' => array(
                        'column' => $this->entity['file']['alias'] . '.height',
                        'comparison' => '=',
                        'value' => $height
                    )))
        );
        return $this->listFiles($filter, $sortorder, $limit, $query_str);
    }

    /**
     * @name            listSoftwareFiles()
     *                  Lists software files.
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->listFiles()
     * 
     * @param           char    $type       Code of type
     * 
     * @return          array   $response
     * 
     */

    public function listSoftwareFiles($type = 's', $filter = null, $sortorder = null, $limit = null, $query_str = null) {
        return $this->listFilesWithType($type, $sortorder, $limit, $query_str);
    }

    /**
     * @name            listVideoFiles()
     *                  Lists video files.
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->listFiles()
     * 
     * @param           char    $type       Code of type
     * 
     * @return          array   $response
     * 
     */

    public function listVideoFiles($type = 'v', $filter = null, $sortorder = null, $limit = null, $query_str = null) {
        return $this->listFilesWithType($type, $sortorder, $limit, $query_str);
    }

    /**
     * @name            updateFile()
     *                  Updates single file. The file must be either a post data (array) or an entity
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->resetResponse()
     * @use             $this->updateFiles()
     * 
     * @param           mixed   $file     entity, id
     * 
     * @return          array   $response
     * 
     */
    public function updateFile($file) {
        return $this->updateFiles(array($file));
    }

    /**
     * @name            updateFiles()
     *                  Updates one or more file details in database.
     * 
     * @since           1.0.0
     * @version         1.0.2
     *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->createException()
     * 
     * @throws          InvalidParameterException
     * 
     * @param           array   $collection     Collection of Product entities or array of entity details.
     * 
     * @return          array   $response
     * 
     */

    public function updateFiles($collection) {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameter', 'Array', 'err.invalid.parameter.collection');
        }
        $countUpdates = 0;
        $updatedItems = array();
        foreach ($collection as $data) {
            if ($data instanceof BundleEntity\File) {
                $entity = $data;
                $this->em->persist($entity);
                $updatedItems[] = $entity;
                $countUpdates++;
            } else if (is_object($data)) {
                if (!property_exists($data, 'id') || !is_numeric($data->id)) {
                    return $this->createException('InvalidParameter', 'Each data must contain a valid identifier id, integer', 'err.invalid.parameter.collection');
                }
                if (!property_exists($data, 'folder')) {
                    $data->folder = 1;
                }
                if (property_exists($data, 'site')) {
                    $data->site = 1;
                }
                $response = $this->getFile($data->id, 'id');
                if ($response['error']) {
                    return $this->createException('EntityDoesNotExist', 'File with id ' . $data->id, 'err.invalid.entity');
                }
                $oldEntity = $response['result']['set'];
                foreach ($data as $column => $value) {
                    $set = 'set' . $this->translateColumnName($column);
                    switch ($column) {
                        case 'local':
                            $localizations = array();
                            foreach ($value as $langCode => $translation) {
                                $localization = $oldEntity->getLocalization($langCode, true);
                                $newLocalization = false;
                                if (!$localization) {
                                    $newLocalization = true;
                                    $localization = new BundleEntity\FileLocalization();
                                    $mlsModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
                                    $response = $mlsModel->getLanguage($langCode, 'iso_code');
                                    $localization->setLanguage($response['result']['set']);
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
                            $response = $sModel->getSite($value, 'id');
                            if (!$response['error']) {
                                $oldEntity->$set($response['result']['set']);
                            } else {
                                new CoreExceptions\SiteDoesNotExistException($this->kernel, $value);
                            }
                            unset($response, $sModel);
                            break;
                        case 'folder':
                            $response = $this->getFileUploadFolder($value, 'id');
                            if (!$response['error']) {
                                $oldEntity->$set($response['result']['set']);
                            } else {
                                new CoreExceptions\EntityDoesNotExistException($this->kernel, $value);
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
            } else {
                new CoreExceptions\InvalidDataException($this->kernel);
            }
        }
        if ($countUpdates > 0) {
            $this->em->flush();
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $updatedItems,
                'total_rows' => $countUpdates,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.update.done',
        );
        return $this->response;
    }

    /**
     * @name            deleteFileUploadFolder()
     *                  Deletes provided folder from database.
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->by_opts
     * @use             $this->createException()
     * @use             $this->delete_entities()
     * @use             $this->doesFileUploadFolderExist()
     * @use             $this->prepare_delete()
     * @use             $this->createQuery()
     * @use             $this->getResult()
     * 
     * @param           array   $collection     Collection consist one of the following: entity,id
     * @param           string  $by             Accepts the $this->by_opts options
     * 
     * @return          array   $response
     * 
     */

    public function deleteFileUploadFolder($collection, $by = 'id') {
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'array()', 'err.invalid.parameter');
        }

        if (!in_array($by, $this->by_opts)) {
            return $this->createException('InvalidByOptionException', implode(',', $this->by_opts), 'err.invalid.parameter.by');
        }

        /** If COLLECTION is ENTITYs then USE ENTITY MANAGER */
        if ($by == 'entity') {
            $sub_response = $this->delete_entities($collection, 'BundleEntity\File');
            if ($sub_response['process'] == 'stop') {
                $mode = 'single';
                if ($sub_response['item_count'] > 1) {
                    $mode = 'multiple';
                }
                $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => $sub_response['entries']['valid'],
                        'total_rows' => $sub_response['item_count'],
                        'last_insert_id' => null,
                    ),
                    'error' => false,
                    'code' => 'scc.entity.deleted.' . $mode,
                );

                return $this->response;
            } else {
                $collection = $sub_response['entries']['invalid'];
            }
        } elseif ($by == 'id') {

            /* If $collection is not ENTITY then use query * */


            /* Defining two arrays which collect exit and not exist record(s) */
            $notexistFolders = array();
            $existFolders = array();
            foreach ($collection as $folder) {
                $entry = $this->doesFileUploadFolderExist($folder, 'id');

                /* Collect exist records */
                if (!$entry['error']) {
                    $existFolders[] = $folder;
                } else {
                    $notexistFolders[] = $folder;
                }
                $entry['error'] = false;
            }

            /*
             * Delete exist records.
             */

            if (count($existFolders) > 0) {

                $table = $this->entity['file_upload_folder']['name'] . ' ' . $this->entity['file_upload_folder']['alias'];
                $q_str = $this->prepare_delete($table, $this->entity['file_upload_folder']['alias'] . '.' . $by, $existFolders);
                $query = $this->em->createQuery($q_str);

                /**
                 * 6. Run query
                 */
                $query->getResult();
            }
            /*
             * Count how many records not exist
             */
            //$count = (count($notexistFolders)>0) ? '.multiple' : '.single';

            if (count($notexistFolders) > 0) {
                $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => $notexistFolders,
                        'total_rows' => count($existFolders),
                        'last_insert_id' => null,
                    ),
                    'error' => true,
                    'code' => 'err.db.record.notfound'
                );
            } else {
                $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => $collection,
                        'total_rows' => count($existFolders),
                        'last_insert_id' => null,
                    ),
                    'error' => false,
                    'code' => 'scc.db.deleted'
                );
            }

            return $this->response;
        }
    }

    /**
     * @name            doesFileUploadFolderExist()
     *                  Checks if file upload folder exist in db.
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->resetResponse()
     * @use             $this->getFileUploadFolder()
     * 
     * @param           mixed   $folder     Entity or entity ID
     * @param           string  $by         By option
     * 
     * @return          array   $response
     * 
     */

    public function doesFileUploadFolderExist($folder, $by = 'id') {
        $this->resetResponse();
        $exist = false;
        $error = false;
        $code = 'scc.db.entry.exist';
        $response = $this->getFileUploadFolder($folder, $by);
        //print_r($response); die;
        if ($response['error'] && $response['result']['total_rows'] < 1) {
            $exist = true;
            $error = true;
            $code = 'err.db.entry.notexist';
        }


        /* Prepare and Return Response */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $exist,
                'total_rows' => $response['result']['total_rows'],
                'last_insert_id' => null,
            ),
            'error' => $error,
            'code' => $code,
        );

        return $this->response;
    }

    /**
     * @name            getFileUploadFolder()
     *                  Returns folder of given ID
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->resetResponse()
     * @use             $this->createException()
     * @use             $this->listFileUploadFolders()
     * 
     * 
     * @throws          InvalidByOptionException
     * @throws          InvalidParameterException
     * @throws          InvalidEntityException
     * 
     * 
     * @param           mixed   $folder     Entity or Entity id of a folder
     * @param           string  $by         By option
     * @return          array   $response
     * 
     */

    public function getFileUploadFolder($folder, $by = 'id') {

        $this->resetResponse();
        if (!in_array($by, $this->by_opts)) {
            return $this->createException('InvalidByOptionException', implode(',', $this->by_opts), 'err.invalid.parameter.by');
        }

        if (!is_object($folder) && !is_numeric($folder) && !is_string($folder)) {
            return $this->createException('InvalidParameterException', 'object,numeric or string', 'err.invalid.parameter.file_upload_folder');
        }
        if ($by == 'entity') {
            if (is_object($folder)) {
                if (!$folder instanceof BundleEntity\FileUploadFolder) {
                    return $this->createException('InvalidEntityException', 'BundleEntity\FileUploadFolder', 'err.invalid.parameter.file_upload_folder');
                }
                /**
                 * Prepare and Return Response
                 */
                $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => $folder,
                        'total_rows' => 1,
                        'last_insert_id' => null,
                    ),
                    'error' => false,
                    'code' => 'scc.entity.found'
                );

                return $this->resetResponse();
            }
        } else {
            $filter[] = array(
                'glue' => '',
                'condition' => array('column' => $this->entity['file_upload_folder']['alias'] . '.' . $by, 'comparison' => '=', 'value' => $folder)
            );

            $response = $this->listFileUploadFolders($filter, null, array('start' => 0, 'count' => 1));
            if ($response['error']) {
                return $response;
            }

            $collection = $response['result']['set'];

            /**
             * Prepare and Return Response
             */
            $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => $collection[0],
                    'total_rows' => count($collection),
                    'last_insert_id' => null,
                ),
                'error' => false,
                'code' => 'scc.entity.found',
            );
            return $this->response;
        }
    }

    /**
     * @name            insertFileUploadFolder()
     *                  Inserts one file attribute into db.
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->listFiles()
     * 
     * @param           mixed   $colletion      Entity or post
     * @param           string  $by             By option
     * 
     * @return          array   $response
     * 
     */

    public function insertFileUploadFolder($collection, $by = 'post') {
        return $this->insertFileUploadFolders($collection, $by);
    }

    /**
     * @name            insertFileUploadFolders()
     *                  Inserts one or more file into db.
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             \BiberLtd\Bundle\FileManagementBundle\Entity\FileUploadFolder
     * @use             $this->createException()
     * @use             $this->insert_entities()
     * 
     * @throws          InvalidParameterException
     * @throws          InvalidMethodException
     * 
     *  
     * @param           mixed   $colletion      Entity or post
     * @param           string  $by             By option
     * 
     * 
     * @return          array   $response
     * 
     */

    public function insertFileUploadFolders($collection, $by = 'post') {
        /* Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'array() or Integer', 'err.invalid.parameter.collection');
        }

        if (!in_array($by, $this->by_opts)) {
            return $this->createException('InvalidParameterException', implode(',', $this->by_opts), 'err.invalid.parameter.by.collection');
        }

        if ($by == 'entity') {
            $sub_response = $this->insert_entities($collection, 'BiberLtd\\Core\\Bundles\\FileManagementBundle\\Entity\\FileUploadFolder');
        } elseif ($by == 'post') {

            /*
             * exptects an array like 
             * 
             * ////////////////////////////////
             * $collection = array(0 => array(
             *      'name'  => 'Test',
             *      'url'   => 'url'
             * )
             * );
             * \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\                                  
             */
            foreach ($collection as $item) {
                $entity = new \BiberLtd\Bundle\FileManagementBundle\Entity\FileUploadFolder();
                foreach ($item['folder'] as $column => $value) {
                    $folder_method = 'set_' . $column;
                    if (method_exists($entity, $folder_method)) {
                        $entity->$folder_method($value);
                    } else {
                        return $this->createException('InvalidMethodException', 'method not found in entity', 'err.method.notfound');
                    }
                }
                $this->em->persist($entity);
            }
            $this->em->flush();
            $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => $collection,
                    'total_rows' => count($collection),
                    'last_insert_id' => $entity->getId(),
                ),
                'error' => false,
                'code' => 'scc.db.insert.done',
            );

            return $this->response;
        }
    }

    /**
     * @name            listFileUploadFolders()
     *                  Lists folders of a given file
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->resetResponse()
     * @use             $this->createException()
     * @use             $this->prepare_where()
     * @use             $this->createQuery()
     * @use             $this->addLimit()
     * @use             $this->getResult()
     * 
     * 
     * $throws          InvalidSortOrderException
     * $throws          InvalidLimitException
     * 
     * 
     * @param           array   $filter     Filter for database query
     * 
     * @return          array   $response
     * 
     */

    public function listFileUploadFolders($filter = null, $sortorder = null, $limit = null, $query_str = null) {
        $this->resetResponse();
        if (!is_array($sortorder) && !is_null($sortorder)) {
            return $this->createException('InvalidSortOrderException', '', 'err.invalid.parameter.sortorder');
        }

        /**
         * Add filter check to below to set join_needed to true
         */
        $order_str = '';
        $where_str = '';
        $group_str = '';
        $filter_str = '';

        /**
         * Start creating the query
         *
         * Note that if no custom select query is provided we will use the below query as a start
         */
        if (is_null($query_str)) {
            $query_str = 'SELECT ' . $this->entity['file_upload_folder']['alias']
                    . ' FROM ' . $this->entity['file_upload_folder']['name'] . ' ' . $this->entity['file_upload_folder']['alias'];
        }

        /**
         * Prepare ORDER BY section of query
         */
        if (!is_null($sortorder)) {
            foreach ($sortorder as $column => $direction) {
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
                        break;
                }
                $order_str .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY ' . $order_str . ' ';
        }

        /**
         * Prepare WHERE section of query
         */

        if (!is_null($filter)) {
            $filter_str = $this->prepare_where($filter);
            $where_str = ' WHERE ' . $filter_str;
        }

        $query_str .= $where_str . $group_str . $order_str;
        $query = $this->em->createQuery($query_str);

        /**
         * Prepare LIMIT section of query
         */

        if (!is_null($limit) && is_numeric($limit)) {
            /*
             * if limit is set
             */
            if (isset($limit['start']) && isset($limit['count'])) {

                $query = $this->addLimit($query, $limit);
            } else {
                $this->createException('InvalidLimitException', '', 'err.invalid.limit');
            }
        }
        $files = $query->getResult();
        $total_rows = count($files);
        if ($total_rows < 1) {
            $this->response['error'] = true;
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }

        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $files,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );



        return $this->response;
    }

    /**
     * @name            listFileUploadFoldersWithLessFilesThan()
     *                  Lists folders which has less files than provided value
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->listFiles()
     * 
     * @param           integer $value      Value of count files
     * 
     * @return          array   $response
     * 
     */

    public function listFileUploadFoldersWithLessFilesThan($value, $sortorder = null, $limit = null, $query_str = null) {
        $filter[] = array(
            'glue' => ' and',
            'condition' => array(
                'column' => $this->entity['file_upload_folder']['alias'] . 'count_files',
                'comparison' => '<',
                'value' => $value
            )
        );
        return $this->listFileUploadFolders($filter, $sortorder, $limit, $query_str);
    }

    /**
     * @name            listFileUploadFoldersWithMoreFilesThan()
     *                  Lists folders which has more files than provided value
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->listFiles()
     * 
     * @param           integer $value      Value of count files
     * 
     * @return          array   $response
     * 
     */

    public function listFileUploadFoldersWithMoreFilesThan($value) {
        $filter[] = array(
            'glue' => ' and',
            'condition' => array(
                'column' => $this->entity['file_upload_folder']['alias'] . 'count_files',
                'comparison' => '>',
                'value' => $value
            )
        );
        return $this->listFileUploadFolders($filter);
    }

    /**
     * @name            listExternalFileUploadFolders()
     *                  This method has no definiton yet
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     */

    public function listExternalFileUploadFolders() {
        
    }

    /**
     * @name            listInternalFileUploadFolders()
     *                  This method has no definiton yet
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     */

    public function listInternalFileUploadFolders() {
        
    }

    /**
     * @name            updateFileUploadFolder()
     *                  Updates single folder of given post data or entity
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->updateFileUploadFolders()
     * 
     * @param           mixed   $collection     Entity or post data
     * @param           string  $by             entity or post
     * 
     * @return          array   $response
     * 
     */

    public function updateFileUploadFolder($collection, $by = 'post') {
        return $this->updateFileUploadFolders($collection, $by);
    }

    /**
     * @name            updateFileUploadFolders()
     *                  Updated one or more folders in database
     * 
     * @since           1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     * 
     * @use             $this->update_entities()
     * @use             $this->createException()
     * @use             $this->listFileUploadFolders()
     * 
     * 
     * @thwors          InvalidParameterException
     * 
     * 
     * @param           mixed   $collection     Entity or post data
     * @param           string  $by             entity or post
     * 
     * @return          array   $response
     * 
     */

    public function updateFileUploadFolders($collection, $by) {
        if ($by == 'entity') {
            $sub_response = $this->update_entities($collection, 'BundleEntity\FileUploadFolder');
            /**
             * If there are items that cannot be deleted in the collection then $sub_Response['process']
             * will be equal to continue and we need to continue process; otherwise we can return response.
             */
            if ($sub_response['process'] == 'stop') {
                $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => $sub_response['entries']['valid'],
                        'total_rows' => $sub_response['item_count'],
                        'last_insert_id' => null,
                    ),
                    'error' => false,
                    'code' => 'scc.db.delete.done',
                );
                return $this->response;
            } else {
                $collection = $sub_response['entries']['invalid'];
            }
        } elseif ($by == 'post') {
            if (!is_array($collection)) {
                return $this->createException('InvalidParameterException', 'expected an array', 'err.invalid.by');
            }

            $foldersToUpdate = array();
            $foldersId = array();
            $count = 0;

            foreach ($collection as $item) {
                if (!isset($item['id'])) {
                    unset($collection[$count]);
                }
                $foldersId[] = $item['id'];
                $foldersToUpdate[$item['id']] = $item;
                $count++;
            }
            $filter = array(
                array(
                    'glue' => 'and',
                    'condition' => array(
                        array(
                            'glue' => 'and',
                            'condition' => array('column' => $this->entity['file_upload_folder']['alias'] . '.id', 'comparison' => 'in', 'value' => $foldersId),
                        )
                    )
                )
            );
            $response = $this->listFileUploadFolders($filter);
            if ($response['error']) {
                return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
            }

            $entities = $response['result']['set'];

            foreach ($entities as $entity) {
                $folderData = $foldersToUpdate[$entity->getId()];
                unset($folderData['id']);
                foreach ($folderData as $column => $value) {
                    $folderMethodSet = 'set_' . $column;
                    $entity->$folderMethodSet($value);
                }
                $this->em->persist($entity);
            }

            $this->em->flush();
        }
    }
}

/**
 * Change Log
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
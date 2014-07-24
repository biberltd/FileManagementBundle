<?php

/**
 * TestController
 *
 * This controller is used to install default / test values to the system.
 * The controller can only be accessed from allowed IP address.
 *
 * @package		FileManagementBundleBundle
 * @subpackage	Controller
 * @name	    TestController
 *
 * @author		Said Imamoglu
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.0
 *
 */

namespace BiberLtd\Core\Bundles\FileManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpKernel\Exception,
    Symfony\Component\HttpFoundation\Response,
    BiberLtd\Core\CoreController;

class TestController extends CoreController {

    /**
     * @name 			init()
     *  		Each controller must call this function as its first statement.27
     *                  This function acts as a constructor and initializes default values of this controller.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     */
    protected function init() {
        $this->init_defaults();
        if (isset($_SERVER['HTTP_CLIENT_IP']) || isset($_SERVER['HTTP_X_FORWARDED_FOR']) || !in_array(@$_SERVER['REMOTE_ADDR'], unserialize(APP_DEV_IPS))
        ) {
            header('HTTP/1.0 403 Forbidden');
            exit('You are not allowed to access this file. Check ' . basename(__FILE__) . ' for more information.');
        }
        /**         * ***************** */
        $this->request = $this->getRequest();
        $this->session = $this->get('session');
        $this->locale = $this->request->getLocale();
        $this->translator = $this->get('translator');
    }

    /**
     * @name 		test_modelAction()
     *  		DOMAIN/test/product/model
     *                  Used to test member localizations.
     *
     * @since		1.0.0
     * @version         1.0.0
     * @author          Said Imamoglu
     *
     */
    public function testAction() {
        /** Initialize */
        $this->init();

        $model = $this->get('core_file_management_bundle.model');
        //echo 1; die;
//        $filter[] = array(
//            'glue' => 'and',
//            'condition' => array(
//                            array(
//                                'glue' => 'and',
//                                'condition' => array('column' => 'p.id', 'comparison' => 'in', 'value' => array(3,4,5,6)),
//                            )
//                        )
//        );
//        $filter[] = array(
//            'glue' => 'and',
//            'condition' => array(
//                array(
//                    'glue' => 'or',
//                    'condition' => array('column' => 'p.status', 'comparison' => 'eq', 'value' => 'a'),
//                ),
//                array(
//                    'glue' => 'and',
//                    'condition' => array('column' => 'p.price', 'comparison' => '<', 'value' => 500),
//                ),
//            )
//        );
        //$sortorder = array('name' => 'desc');
        //$limit = null;
        // $response = $model->list_products_liked_less_than(6, $sortorder, $limit);
        //$response = $model->deleteFile("13", 'id');
        //$response = $model->getFile(14);
        //$response = $model->listFiles();
        //$response   =   $model->doesFileExist(12);
        //$response   = $model->listFilesInFolder(1);
        //$response   = $model->listFilesOfSite(1);
        //$response   = $model->listFilesWithExtension(1);
        //$response   = $model->listFilesWithType('a');
        //$response   = $model->listSoftwareFiles();
        //$response   = $model->listImageFilesWithDimension(300,500);
        //$response   = $model->insertFile(array(1,2));

        /*
          $type='entity';
          if($type=='entity'){
          $file = new \BiberLtd\Core\Bundles\FileManagementBundle\Entity\File();
          $folder = new \BiberLtd\Core\Bundles\FileManagementBundle\Entity\FileUploadFolder();
          $response = $folder->
          $folder->setName('FOLDER');
          $folder->setUrlKey('URL_KEY');
          $folder->setPathAbsolute('PATH');
          $folder->setUrl('URL');
          $folder->setType('i');
          $folder->setAllowedMaxSize('AMH');
          $folder->setAllowedMinSize('AMS');

          $file->setName('ANEYYY');
          $file->setUrlKey('URL_KEY');
          $file->setSourceOriginal('SOURCE_ORIGINAL');
          $file->setType('i');
          $file->setMimeType('MIME_TYPE');
          $file->setExtension('EZXTENSION');
          $file->set_file_upload_folder($folder);
          //$file->setFile(1);
          //$file->setSite(1);
          //echo '<pre>';print_r($file); die;
          $this->em = $this->getDoctrine()->getManager();
          $this->em->persist($file);
          $this->em->flush();
          } else {
          //$this->
          }
          echo 'okdir başgan'; die;
         * 
         */
        echo '<pre>';

        //$response = $model->deleteFileUploadFolder(array(23));
        //$response = $model->deleteFile(array(10,11,12,24));
        //$response = $model->deleteFile(array(18,19,20,21,22,23,24,25));
        /*
         * 

          $response = $model->insertFile(array(
          array(
          'name'      =>'Ahmet',
          'url_key'   => 'url_key',
          'source_original'=> 'source_original',
          'type'          => 'i',
          //'folder'        =>25,
          'mime_type'     =>'',
          'extension'     => 'ext'

          )
          ));
         */
        $data =
            array(
                     'id' => 38,
                     'name' => 'değiştim ben',
                    'folder' => array(
                        'id'    => 39,
                        'name'  => 'bu ne be'
                    )
            );

        $response = $model->updateFile($data);





        //echo '<pre>';
        var_dump($response);
        die;

        if (!$response['error']) {

            $entries = $response['result']['set'];
            $message = '';

            foreach ($entries as $entry) {
                $message .= $entry->getId();
            }
            $html = '<html><head></head><body>' . $message . '</body></html>';
            return new Response($html);
        }
        $html = '<html><head></head><body>Not found!</body></html>';
        return new Response($html);
    }

}

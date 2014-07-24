<?php

/**
 * AjaxController
 *
 * This controller handles all ajax requests.
 *
 * @package		Controller
 * @subpackage	Controller
 * @name	    FrontEndController
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.0
 *
 */

namespace BiberLtd\Core\Bundles\FileManagementBundle\Controller;

use BiberLtd\Core\CoreController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
/** Models to be used */
use BiberLtd\Core\Bundles\ContentManagementBundle\Services as CMSService;
use BiberLtd\Core\Bundles\MultiLanguageSupportBundle\Services as MLSService;
use BiberLtd\Core\Bundles\GalleryBundle\Services as GService;
use BiberLtd\Core\Bundles\SocialNetworkBundle\Services as SNBService;
use BiberLtd\Core\Bundles\ProductManagementBundle\Services as PMBService;
use BiberLtd\Core\Bundles\FileManagementBundle\Entity as FMBEntity;

class AjaxController extends CoreController {

    /**
     * @name            getImagesAction()
     *                  DOMAIN/{_locale}/process/contact
     *
     *                  Sends contact request to oli email address.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *     *
     * @return          \Symfony\Component\HttpFoundation\Response
     */
    public function getImagesAction($id) {

        /**         * ********************************************************************
         * 1. LOAD REQUIRED ELEMENTS / GEREKLİ SERVİSLERİ ÇAĞIR
         * [en]
         * In most cases you will need to load the translator for multi language
         * support, the request to handle HTTP requests, the session for session
         * related processings, and global services input validator for input
         * validation, access validator to secure access to the controller and
         * the session manager to access ready-to-use session functionality.
         *
         * [tr]
         * Çoğunlukla request objesine HTTP requestler ile çalışmak için,
         * translator objesine çoklu dil desteği için, session objesine oturum
         * ile ilgili işlemleri yapabilmek için ve global servislerden input
         * validator objesine kullanıcı verilerini kontrol edebilmek, access
         * validator objesine sayfaya erişimi kontrol edebilmek için ve session
         * manager servisine sesson ile ilgili kullanıma hazır işlevlerden
         * faydalanabilmek için ihtiyacınız olacaktır.
         *
         * $request = $this->get('request');
         * $translator = $this->get('translator');
         * $session = $this->get('session');
         * $iv = $this->get('input_validator')
         * $av = $this->get('access_validator')
         * $sm = $this->get('session_manager')
         *
         * ******************************************************************** */
        $request = $this->get('request');
        $translator = $this->get('translator');
        $session = $this->get('session');
        $referrer = $request->headers->get('referer');
        $iv = $this->get('input_validator');
        $av = $this->get('access_validator');
        $sm = $this->get('session_manager');
        $this->setURLs('cms');

        $current_language = $this->get('session')->get('_locale');

        $galleryId = $id;
        $iv->set_input(array($galleryId));
        if ($iv->is_empty()) {
            return new Response('err.gallery_id.empty');
        }
        if (!$iv->is_integer()) {
            return new Response('err.gallery_id.invalid');
        }
        $gm = $this->get('gallery.model');

        $response = $gm->getGallery($galleryId, 'id');

        if($response['error']){
            return new Response('err.gallery.notexist');
        }

        $gallery = $response['result']['set'];
        unset($response);

        $response = $gm->listImagesOfGallery($gallery, null, array('sort_order' => 'asc'));
        $galleryMedia = array();
        if(!$response['error']){ 
            $galleryMedia = $response['result']['set'];
        }
        unset($response);
        $result = array();
        $files = array();
        foreach($galleryMedia as $media){
            $image = $media->getFile();
            $file = array(
                'name'      => $image->getName(),
                'size'      => $image->getSize(),
                'url'       => $this->url['domain'].$image->getFolder()->getPathAbsolute().$image->getSourceOriginal(),
                'thumbnail_url' => $this->url['domain'].$image->getFolder()->getPathAbsolute().'thumbs/'.$image->getSourcePreview(),
                'delete_url' => $this->url['base_l'].'/manage/gallery/file/delete/'.$image->getId(),
                'delete_type'=> 'DELETE',
                'file_id'   => $image->getId(),
                'sort'      => $media->getSortOrder(),
                'extra'     => $media->getGallery()->getId(),
            );
            $files[] = $file;
        }
        $result['files'] = $files;
        return new JsonResponse($result);
    }

    /**
     * @name            contactAction()
     *                  DOMAIN/{_locale}/process/contact
     *
     *                  Sends contact request to oli email address.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *     *
     * @return          \Symfony\Component\HttpFoundation\Response
     */
    public function uploadAction($id) {
        /**
         * ********************************************************************
         * 1. LOAD REQUIRED ELEMENTS / GEREKLİ SERVİSLERİ ÇAĞIR
         * [en]
         * In most cases you will need to load the translator for multi language
         * support, the request to handle HTTP requests, the session for session
         * related processings, and global services input validator for input
         * validation, access validator to secure access to the controller and
         * the session manager to access ready-to-use session functionality.
         *
         * [tr]
         * Çoğunlukla request objesine HTTP requestler ile çalışmak için,
         * translator objesine çoklu dil desteği için, session objesine oturum
         * ile ilgili işlemleri yapabilmek için ve global servislerden input
         * validator objesine kullanıcı verilerini kontrol edebilmek, access
         * validator objesine sayfaya erişimi kontrol edebilmek için ve session
         * manager servisine sesson ile ilgili kullanıma hazır işlevlerden
         * faydalanabilmek için ihtiyacınız olacaktır.
         *
         * $request = $this->get('request');
         * $translator = $this->get('translator');
         * $session = $this->get('session');
         * $iv = $this->get('input_validator')
         * $av = $this->get('access_validator')
         * $sm = $this->get('session_manager')
         *
         * ********************************************************************
         */
        $request = $this->get('request');
        $translator = $this->get('translator');
        $session = $this->get('session');
        $referrer = $request->headers->get('referer');
        $iv = $this->get('input_validator');
        $av = $this->get('access_validator');
        $sm = $this->get('session_manager');
        /**
         * ********************************************************************
         *
         * 2. GET THE CURRENT URL BASE / ANA URL YAPISINI OLUŞTUR
         * [en]
         * Simply keep this as is and use $url_base_l variable to build your
         * complete URLs.
         *
         * [tr]
         * Bu alana dokunmayın. Kodunuzun geri kalanında $url_base_l değişkenini
         * URL oluşturmak için kullanabilirsiniz.
         *
         * *********************************************************************
         *
         * $url_base_l => HTTP(S)://DOMAIN/
         *
         * ******************************************************************** */
        $this->setURLs('cms');

        $current_language = $this->get('session')->get('_locale');
        /**         * ********************************************************************
         *
         * 3. VALIDATE ACCESS / ERİŞİM HAKLARINI DENETLE
         * [en]
         * Here we validate access to the page. You have to create a variable
         * named $access_map as defined below.
         *
         * NOTE: THIS WILL BE CONNECTED TO AccessManagementBundle and automatized.
         *
         * [tr]
         * Burada sayfaya erişimi kontrol ediyoruz. Erişim kontrolünü düzenlemek
         * için aşağıda tanımlandığı şekilde $access_map diye bir değişken
         * oluşturmalısınız.
         *
         * NOT: BU ALAN AccessManagementBundle ile birleştirilerek otomotize
         * edilecektir.
         *
         * *********************************************************************
         *
         * $access_map = array(
         *              'unmanaged' => false, // false or true
         *              'groups'    => array(), // empty or a list of group codes
         *              'guest'     => false, // false or true,
         *              'members'   => array(), // empty or a list of member ids
         *              'authenticated' => true, // true or false
         *              'status'    => array('a'), // List of array codes or empty.
         * );
         *
         * ******************************************************************** */

        /**
         * Check if member has enough access rights
         * @todo AccessManagementBundle ile birleştirilecek
         */
        $access_map = array(
            'unmanaged' => true,
            'groups' => array(),
            'guest' => false,
            'members' => array(),
            'authenticated' => false,
            'status' => array(),
        );

        if (!$av->has_access(null, $access_map)) {
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'error');
            /** $response[$code] must have a corresponding translation */
            $session->getFlashBag()->add('msg.content', $translator->trans('err.invalid.rights', array(), 'sys'));
            if (isset($referrer) && $referrer != false) {
                return new Response('err.invalid.access');
            }
            return new Response('err.invalid.access');
        }
        /**
         * ********************************************************************
         *
         * 4. AJAX FUNCTIONALITIES
         *
         * ******************************************************************** */
        $galleryId = $id;
        $iv->set_input(array($galleryId));
        if ($iv->is_empty($galleryId)) {
            return new Response('err.gallery_id.empty');
        }
        if (!$iv->is_integer($galleryId)) {
            return new Response('err.gallery_id.invalid');
        }

        /** DO UPLOAD */
        /** BEGIN upload file */
        $rootDir = $this->get('kernel')->getRootDir();
        $folderId = 1;
        $FMM = $this->get('filemanagement.model');
        $response = $FMM->getFileUploadFolder($folderId, 'id');
        if ($response['error']) {
            return array('err', 'File upload folder not found');
            exit;
        }
        $folderEntity = $response['result']['set'];
        unset($response);

        $absolutePath = $folderEntity->getPathAbsolute();
        $folder = rtrim($rootDir . '/../www' . $absolutePath, "/");
        $file = $request->files->get('files');
        if ($file instanceof UploadedFile) {
            $origName = $file->getClientOriginalName();
            $nameArray = explode('.', $origName);
            $fileType = $nameArray[count($nameArray) - 1];
            $fileName = md5($origName . time());
            $fileSize = $file->getSize();
            $validFileTypes = array('jpg', 'jpeg', 'png', 'bmp');
            $newFileFullName = $fileName . '.' . $fileType;
            $mimeType = $file->getClientMimeType();
            if (in_array(strtolower($fileType), $validFileTypes)) {
                $newFile = $file->move($folder, $newFileFullName);
            } else {
                return array('err', 'File type is not allowed');
            }
        } else {
            return array('err', 'Invalid object');
        }

        /**
         * SAVING FILE INFO TO DB
         */
        $SMM = $this->get('sitemanagement.model');
        $response = $SMM->getSite(1);
        if ($response['error']) {
            return array('err', 'Site not found');
        }
        $siteEntity = $response['result']['set'];
        unset($response);

        $fileEntity = new FMBEntity\File();
        $fileEntity->setName($newFileFullName);
        $fileEntity->setUrlKey($fileName);
        $fileEntity->setSourceOriginal($newFileFullName);
        $fileEntity->setSourcePreview($newFileFullName);
        $fileEntity->setType('i');
        $fileEntity->setFolder($folderEntity);
        $fileEntity->setSite($siteEntity);
        $fileEntity->setExtension($fileType);
        $fileEntity->setSize($fileSize);
        $fileEntity->setMimeType($mimeType);

        $response = $FMM->insertFile($fileEntity);
        if ($response['error']) {
            return array('err', 'Db insert failed.');
        }
        $insertedFile = $response['result']['set'][0];
        /**
         * ADDING FILE TO GALLERY
         */
        $gallery = '';
        $GB = $this->get('gallery.model');
        $response = $GB->getGallery($id, 'id');
        if ($response['error']) {
            return array('err', 'Gallery not found');
        }
        $gallery = $response['result']['set'];
        unset($response);

        $files = array('sortorder'=> null,'file'=>$insertedFile);

        $response = $GB->addFileToGallery($files,$gallery);
        if ($response['error']) {
            return array('err','Image is not added to gallery');
        }

        $filePath = $folder.'/'.$fileName.'.'.$fileType;
        $fileNameWithExt = $fileName.'.'.$fileType;
        $thumbFolderPath = $folder.'/thumbs';
        $thumPath = $thumbFolderPath.'/'.$fileName.'.'.$fileType;

        $fileUrl = $this->prepareUrl(false, false) . $absolutePath . $fileName . '.' . $fileType;
        $thumbUrl = $this->prepareUrl(false, false) . $absolutePath.'thumbs/' . $fileName . '.' . $fileType;
        $returned = array();
        $iwModel = $this->get('imageworkshop.model');
        $imageLayer = $iwModel->initFromPath($filePath);
        $imageLayer->resizeByLargestSideInPixel(80, true);
        $imageLayer->save($thumbFolderPath, $fileNameWithExt, true, null, 80);

        $returned['files'][0]['name'] = $newFileFullName;
        $returned['files'][0]['size'] = $fileSize;
        $returned['files'][0]['url'] = $fileUrl;
        $returned['files'][0]['thumbnail_url'] = $thumbUrl;
        $returned['files'][0]['delete_url'] = $this->url['base_l'] . '/manage/gallery/file/delete/'.$insertedFile->getId();
        $returned['files'][0]['delete_type'] = 'post';
        $returned['files'][0]['file_id'] = $insertedFile->getId();
        $returned['files'][0]['extra'] = $id;

        return new Response(json_encode($returned));
    }

    /**
     * @name            deleteFileAction ()
     *                  Deletes a single file both from database and file structure.
     *
     * @since           1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @see             BiberLtd\Core\Bundles\CoreBundle\Resources\views\cms\Modules\widget.multi_upload.html.smarty
     *
     * @param           integer         $id
     *
     * @return          string
     */
    public function deleteFileAction($id){
        /**
         * @todo Access management
         */
        $this->setURLs('cms');

        $id = (int) $id;
        $fModel = $this->get('filemanagement.model');

        $response = $fModel->getFile($id, 'id');

        if($response['error']){
            return new Response('err.file.notexist');
        }
        $file = $response['result']['set'];
        $filePath = $_SERVER['DOCUMENT_ROOT'].$file->getFolder()->getPathAbsolute().$file->getSourceOriginal();
        $thumbPath = $_SERVER['DOCUMENT_ROOT'].$file->getFolder()->getPathAbsolute().'thumbs/'.$file->getSourcePreview();
        if(file_exists($filePath) && !is_dir($filePath)){
            unlink($filePath);
        }
        if(file_exists($thumbPath) && !is_dir($thumbPath)){
            unlink($thumbPath);
        }
        $fModel->deleteFile($file);

        return new Response('success');
    }
    /**
     * @name            sortFileAction ()
     *                  Updates sort order of file.
     *
     * @since           1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @see             BiberLtd\Core\Bundles\CoreBundle\Resources\views\cms\Modules\widget.multi_upload.html.smarty
     *
     * @param           integer         $id
     *
     * @return          string
     */
    public function sortFileAction(){
        /**
         * @todo Access management
         */
        $this->setURLs('cms');

        $request = $this->get('request');

        $id = (int) str_replace('sortorder-', '', $request->get('id'));
        $order = (int) $request->get('order');
        $gallery = (int) $request->get('extra');

        $gModel = $this->get('gallery.model');

        $response = $gModel->getGalleryMedia($id, $gallery);

        if($response['error']){
            return new Response('err.file.notexist');
        }
        $galleryMedia = $response['result']['set'];

        $galleryMedia->setSortOrder($order);

        $gModel->updateGalleryMedia($galleryMedia);

        return new Response('success');
    }
}

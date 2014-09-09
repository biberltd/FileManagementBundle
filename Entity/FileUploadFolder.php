<?php
/**
 * @name        FileUploadFolder
 * @package		BiberLtd\Bundle\CoreBundle\FileManagementBundle
 *
 * @author		Murat Ünal
 * @version     1.0.1
 * @date        09.09.2013
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */
namespace BiberLtd\Bundle\FileManagementBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use BiberLtd\Bundle\CoreBundle\CoreEntity;

/** 
 * @ORM\Entity
 * @ORM\Table(
 *     name="file_upload_folder",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="idx_u_file_upload_folder_id", columns={"id"}),
 *         @ORM\UniqueConstraint(name="idx_u_file_upload_folder_url_key", columns={"url_key","site"})
 *     }
 * )
 */
class FileUploadFolder extends CoreEntity
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", length=10)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=155, nullable=false)
     */
    private $name;

    /** 
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $url_key;

    /** 
     * @ORM\Column(type="text", nullable=false)
     */
    private $path_absolute;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $url;

    /** 
     * @ORM\Column(type="string", length=1, nullable=false)
     */
    private $type;

    /** 
     * @ORM\Column(type="decimal", length=5, nullable=true)
     */
    private $allowed_max_size;

    /** 
     * @ORM\Column(type="decimal", length=5, nullable=true)
     */
    private $allowed_min_size;

    /** 
     * @ORM\Column(type="integer", length=5, nullable=true)
     */
    private $allowed_max_width;

    /** 
     * @ORM\Column(type="integer", length=5, nullable=true)
     */
    private $allowed_min_width;

    /** 
     * @ORM\Column(type="integer", length=5, nullable=true)
     */
    private $allowed_max_height;

    /** 
     * @ORM\Column(type="integer", length=5, nullable=true)
     */
    private $allowed_min_height;

    /** 
     * @ORM\Column(type="integer", length=10, nullable=false)
     */
    private $count_files;

    /** 
     * @ORM\OneToMany(targetEntity="BiberLtd\Bundle\FileManagementBundle\Entity\File", mappedBy="folder")
     */
    private $files;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $site;
    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

    /**
     * @name            getId()
     *                  Gets $id property.
     * .
     * @author          Murat Ünal
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->id
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @name                  setAllowedMaxHeight ()
     *                                            Sets the allowed_max_height property.
     *                                            Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $allowed_max_height
     *
     * @return          object                $this
     */
    public function setAllowedMaxHeight($allowed_max_height) {
        if(!$this->setModified('allowed_max_height', $allowed_max_height)->isModified()) {
            return $this;
        }
		$this->allowed_max_height = $allowed_max_height;
		return $this;
    }

    /**
     * @name            getAllowedMaxHeight ()
     *                                      Returns the value of allowed_max_height property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->allowed_max_height
     */
    public function getAllowedMaxHeight() {
        return $this->allowed_max_height;
    }

    /**
     * @name                  setAllowedMaxSize ()
     *                                          Sets the allowed_max_size property.
     *                                          Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $allowed_max_size
     *
     * @return          object                $this
     */
    public function setAllowedMaxSize($allowed_max_size) {
        if(!$this->setModified('allowed_max_size', $allowed_max_size)->isModified()) {
            return $this;
        }
		$this->allowed_max_size = $allowed_max_size;
		return $this;
    }

    /**
     * @name            getAllowedMaxSize ()
     *                                    Returns the value of allowed_max_size property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->allowed_max_size
     */
    public function getAllowedMaxSize() {
        return $this->allowed_max_size;
    }

    /**
     * @name                  setAllowedMaxWidth ()
     *                                           Sets the allowed_max_width property.
     *                                           Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $allowed_max_width
     *
     * @return          object                $this
     */
    public function setAllowedMaxWidth($allowed_max_width) {
        if(!$this->setModified('allowed_max_width', $allowed_max_width)->isModified()) {
            return $this;
        }
		$this->allowed_max_width = $allowed_max_width;
		return $this;
    }

    /**
     * @name            getAllowedMaxWidth ()
     *                                     Returns the value of allowed_max_width property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->allowed_max_width
     */
    public function getAllowedMaxWidth() {
        return $this->allowed_max_width;
    }

    /**
     * @name                  setAllowedMinHeight ()
     *                                            Sets the allowed_min_height property.
     *                                            Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $allowed_min_height
     *
     * @return          object                $this
     */
    public function setAllowedMinHeight($allowed_min_height) {
        if(!$this->setModified('allowed_min_height', $allowed_min_height)->isModified()) {
            return $this;
        }
		$this->allowed_min_height = $allowed_min_height;
		return $this;
    }

    /**
     * @name            getAllowedMinHeight ()
     *                                      Returns the value of allowed_min_height property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->allowed_min_height
     */
    public function getAllowedMinHeight() {
        return $this->allowed_min_height;
    }

    /**
     * @name                  setAllowedMinSize ()
     *                                          Sets the allowed_min_size property.
     *                                          Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $allowed_min_size
     *
     * @return          object                $this
     */
    public function setAllowedMinSize($allowed_min_size) {
        if(!$this->setModified('allowed_min_size', $allowed_min_size)->isModified()) {
            return $this;
        }
		$this->allowed_min_size = $allowed_min_size;
		return $this;
    }

    /**
     * @name            getAllowedMinSize ()
     *                                    Returns the value of allowed_min_size property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->allowed_min_size
     */
    public function getAllowedMinSize() {
        return $this->allowed_min_size;
    }

    /**
     * @name                  setAllowedMinWidth ()
     *                                           Sets the allowed_min_width property.
     *                                           Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $allowed_min_width
     *
     * @return          object                $this
     */
    public function setAllowedMinWidth($allowed_min_width) {
        if(!$this->setModified('allowed_min_width', $allowed_min_width)->isModified()) {
            return $this;
        }
		$this->allowed_min_width = $allowed_min_width;
		return $this;
    }

    /**
     * @name            getAllowedMinWidth ()
     *                                     Returns the value of allowed_min_width property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->allowed_min_width
     */
    public function getAllowedMinWidth() {
        return $this->allowed_min_width;
    }

    /**
     * @name                  setCountFiles ()
     *                                      Sets the count_files property.
     *                                      Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $count_files
     *
     * @return          object                $this
     */
    public function setCountFiles($count_files) {
        if(!$this->setModified('count_files', $count_files)->isModified()) {
            return $this;
        }
		$this->count_files = $count_files;
		return $this;
    }

    /**
     * @name            getCountFiles ()
     *                                Returns the value of count_files property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->count_files
     */
    public function getCountFiles() {
        return $this->count_files;
    }

    /**
     * @name                  setFiles ()
     *                                 Sets the files property.
     *                                 Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $files
     *
     * @return          object                $this
     */
    public function setFiles($files) {
        if(!$this->setModified('files', $files)->isModified()) {
            return $this;
        }
		$this->files = $files;
		return $this;
    }

    /**
     * @name            getFiles ()
     *                           Returns the value of files property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->files
     */
    public function getFiles() {
        return $this->files;
    }

    /**
     * @name                  setName ()
     *                                Sets the name property.
     *                                Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $name
     *
     * @return          object                $this
     */
    public function setName($name) {
        if(!$this->setModified('name', $name)->isModified()) {
            return $this;
        }
		$this->name = $name;
		return $this;
    }

    /**
     * @name            getName ()
     *                          Returns the value of name property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @name                  setPathAbsolute ()
     *                                        Sets the path_absolute property.
     *                                        Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $path_absolute
     *
     * @return          object                $this
     */
    public function setPathAbsolute($path_absolute) {
        if(!$this->setModified('path_absolute', $path_absolute)->isModified()) {
            return $this;
        }
		$this->path_absolute = $path_absolute;
		return $this;
    }

    /**
     * @name            getPathAbsolute ()
     *                                  Returns the value of path_absolute property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->path_absolute
     */
    public function getPathAbsolute() {
        return $this->path_absolute;
    }

    /**
     * @name                  setSite ()
     *                                Sets the site property.
     *                                Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $site
     *
     * @return          object                $this
     */
    public function setSite($site) {
        if(!$this->setModified('site', $site)->isModified()) {
            return $this;
        }
		$this->site = $site;
		return $this;
    }

    /**
     * @name            getSite ()
     *                          Returns the value of site property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->site
     */
    public function getSite() {
        return $this->site;
    }

    /**
     * @name                  setType ()
     *                                Sets the type property.
     *                                Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $type
     *
     * @return          object                $this
     */
    public function setType($type) {
        if(!$this->setModified('type', $type)->isModified()) {
            return $this;
        }
		$this->type = $type;
		return $this;
    }

    /**
     * @name            getType ()
     *                          Returns the value of type property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->type
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @name                  setUrl ()
     *                               Sets the url property.
     *                               Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $url
     *
     * @return          object                $this
     */
    public function setUrl($url) {
        if(!$this->setModified('url', $url)->isModified()) {
            return $this;
        }
		$this->url = $url;
		return $this;
    }

    /**
     * @name            getUrl ()
     *                         Returns the value of url property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->url
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * @name                  setUrlKey ()
     *                                  Sets the url_key property.
     *                                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $url_key
     *
     * @return          object                $this
     */
    public function setUrlKey($url_key) {
        if(!$this->setModified('url_key', $url_key)->isModified()) {
            return $this;
        }
		$this->url_key = $url_key;
		return $this;
    }

    /**
     * @name            getUrlKey ()
     *                            Returns the value of url_key property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->url_key
     */
    public function getUrlKey() {
        return $this->url_key;
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.1                      Murat Ünal
 * 09.09.2013
 * **************************************
 * A getAllowedMaxWidth()
 * A getAllowedMaxHeight()
 * A getAllowedMaxSize()
 * A getAllowedMinHeight()
 * A getAllowedMinWidth()
 * A getCountFiles()
 * A getFile()
 * A getId()
 * A getName()
 * A getPathAbsolute()
 * A getSite()
 * A getType()
 * A getUrl()
 * A getUrlKey()
 * A setAllowedMaxWidth()
 * A setAllowedMaxHeight()
 * A setAllowedMaxSize()
 * A setAllowedMinHeight()
 * A setAllowedMinWidth()
 * A setCountFiles()
 * A setFile()
 * A setName()
 * A setPathAbsolute()
 * A setSite()
 * A setType()
 * A setUrl()
 * A setUrlKey()
 *
 */
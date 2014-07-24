<?php
/**
 * @name        File
 * @package		BiberLtd\Core\FileManagementBundle
 *
 * @author      Can Berkol
 * @author		Murat Ünal
 * @version     1.0.3
 * @date        17.07.2014
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */
namespace BiberLtd\Core\Bundles\FileManagementBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use \BiberLtd\Core\CoreLocalizableEntity;


/** 
 * @ORM\Entity
 * @ORM\Table(
 *     name="file",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={
 *         @ORM\Index(name="idx_n_file_mime_type", columns={"mime_type"}),
 *         @ORM\Index(name="idx_n_file_extension", columns={"extension"}),
 *         @ORM\Index(name="idx_n_file_dimension", columns={"width","height"})
 *     },
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idx_u_file_id", columns={"id"})}
 * )
 */
class File extends CoreLocalizableEntity
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", length=15)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=165, nullable=false)
     */
    private $name;

    /** 
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $url_key;

    /** 
     * @ORM\Column(type="text", nullable=false)
     */
    private $source_original;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    private $source_preview;

    /** 
     * @ORM\Column(type="string", length=1, nullable=false)
     */
    private $type;

    /** 
     * @ORM\Column(type="integer", length=5, nullable=true)
     */
    private $width;

    /** 
     * @ORM\Column(type="integer", length=5, nullable=true)
     */
    private $height;

    /** 
     * @ORM\Column(type="decimal", unique=true, length=5, nullable=false)
     */
    private $size;

    /** 
     * @ORM\Column(type="string", length=45, nullable=false)
     */
    private $mime_type;

    /** 
     * @ORM\Column(type="string", length=6, nullable=false)
     */
    private $extension;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $tags;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $exif;

    /** 
     * @ORM\OneToMany(
     *     targetEntity="BiberLtd\Core\Bundles\MemberManagementBundle\Entity\FilesOfMember",
     *     mappedBy="file"
     * )
     */
    private $files_of_members;

    /**
     * @ORM\OneToMany(
     *     targetEntity="BiberLtd\Core\Bundles\FileManagementBundle\Entity\FileLocalization",
     *     mappedBy="file",
     *     cascade={"persist"}
     * )
     */
    protected $localizations;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Core\Bundles\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="CASCADE")
     */
    private $site;

    /** 
     * @ORM\ManyToOne(
     *     targetEntity="BiberLtd\Core\Bundles\FileManagementBundle\Entity\FileUploadFolder",
     *     inversedBy="files"
     * )
     * @ORM\JoinColumn(name="folder", referencedColumnName="id", nullable=false)
     */
    private $folder;

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
     * @name                  setExtension ()
     *                                     Sets the extension property.
     *                                     Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $extension
     *
     * @return          object                $this
     */
    public function setExtension($extension) {
        if(!$this->setModified('extension', $extension)->isModified()) {
            return $this;
        }
		$this->extension = $extension;
		return $this;
    }

    /**
     * @name            getExtension ()
     *                               Returns the value of extension property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->extension
     */
    public function getExtension() {
        return $this->extension;
    }

    /**
     * @name                  setFilesOfMembers ()
     *                                          Sets the files_of_members property.
     *                                          Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $files_of_members
     *
     * @return          object                $this
     */
    public function setFilesOfMembers($files_of_members) {
        if(!$this->setModified('files_of_members', $files_of_members)->isModified()) {
            return $this;
        }
		$this->files_of_members = $files_of_members;
		return $this;
    }

    /**
     * @name            getFilesOfMembers ()
     *                                    Returns the value of files_of_members property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->files_of_members
     */
    public function getFilesOfMembers() {
        return $this->files_of_members;
    }

    /**
     * @name                  setFolder ()
     *                                  Sets the folder property.
     *                                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $folder
     *
     * @return          object                $this
     */
    public function setFolder($folder) {
        if(!$this->setModified('folder', $folder)->isModified()) {
            return $this;
        }
		$this->folder = $folder;
		return $this;
    }

    /**
     * @name            getFolder ()
     *                            Returns the value of folder property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->folder
     */
    public function getFolder() {
        return $this->folder;
    }

    /**
     * @name                  setHeight ()
     *                                  Sets the height property.
     *                                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $height
     *
     * @return          object                $this
     */
    public function setHeight($height) {
        if(!$this->setModified('height', $height)->isModified()) {
            return $this;
        }
		$this->height = $height;
		return $this;
    }

    /**
     * @name            getHeight ()
     *                            Returns the value of height property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->height
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * @name                  setMimeType ()
     *                                    Sets the mime_type property.
     *                                    Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $mime_type
     *
     * @return          object                $this
     */
    public function setMimeType($mime_type) {
        if(!$this->setModified('mime_type', $mime_type)->isModified()) {
            return $this;
        }
		$this->mime_type = $mime_type;
		return $this;
    }

    /**
     * @name            getMimeType ()
     *                              Returns the value of mime_type property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->mime_type
     */
    public function getMimeType() {
        return $this->mime_type;
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
     *                  Returns the value of name property.
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
     * @name            setSite ()
     *                  Sets the site property.
     *                  Updates the data only if stored value and value to be set are different.
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
     * @name            setSize ()
     *                  Sets the size property.
     *                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $size
     *
     * @return          object                $this
     */
    public function setSize($size) {
        if(!$this->setModified('size', $size)->isModified()) {
            return $this;
        }
		$this->size = $size;
		return $this;
    }

    /**
     * @name            getSize ()
     *                          Returns the value of size property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->size
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @name                  setSourceOriginal ()
     *                                          Sets the source_original property.
     *                                          Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $source_original
     *
     * @return          object                $this
     */
    public function setSourceOriginal($source_original) {
        if(!$this->setModified('source_original', $source_original)->isModified()) {
            return $this;
        }
		$this->source_original = $source_original;
		return $this;
    }

    /**
     * @name            getSourceOriginal ()
     *                  Returns the value of source_original property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->source_original
     */
    public function getSourceOriginal() {
        return $this->source_original;
    }

    /**
     * @name            setSourcePreview ()
     *                  Sets the source_preview property.
     *                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $source_preview
     *
     * @return          object                $this
     */
    public function setSourcePreview($source_preview) {
        if(!$this->setModified('source_preview', $source_preview)->isModified()) {
            return $this;
        }
		$this->source_preview = $source_preview;
		return $this;
    }

    /**
     * @name            getSourcePreview ()
     *                                   Returns the value of source_preview property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->source_preview
     */
    public function getSourcePreview() {
        return $this->source_preview;
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
     *                  Returns the value of url_key property.
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

    /**
     * @name                  setWidth ()
     *                                 Sets the width property.
     *                                 Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $width
     *
     * @return          object                $this
     */
    public function setWidth($width) {
        if(!$this->setModified('width', $width)->isModified()) {
            return $this;
        }
		$this->width = $width;
		return $this;
    }

    /**
     * @name            getWidth ()
     *                  Returns the value of width property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->width
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * @name            setExif ()
     *                  Sets the exif property.
     *                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $exif
     *
     * @return          object                $this
     */
    public function setExif($exif) {
        if($this->setModified('exif', $exif)->isModified()) {
            $this->exif = $exif;
        }

        return $this;
    }

    /**
     * @name            getExif ()
     *                  Returns the value of exif property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->exif
     */
    public function getExif() {
        return $this->exif;
    }

    /**
     * @name            setTags ()
     *                  Sets the tags property.
     *                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $tags
     *
     * @return          object                $this
     */
    public function setTags($tags) {
        if($this->setModified('tags', $tags)->isModified()) {
            $this->tags = $tags;
        }

        return $this;
    }

    /**
     * @name            getTags ()
     *                  Returns the value of tags property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->tags
     */
    public function getTags() {
        return $this->tags;
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.3                      Can Berkol
 * 10.10.2013
 * **************************************
 * localizations property added.
 *
 * **************************************
 * v1.0.2                      Murat Ünal
 * 10.10.2013
 * **************************************
 * A getExtension()
 * A get_file_upload_folder()
 * A get_files_of_members()
 * A getHeight()
 * A getId()
 * A get_mine_type()
 * A getName()
 * A getSite()
 * A getSize()
 * A getSourceOriginal()
 * A getSourcePreview()
 * A getType()
 * A getUrlKey()
 * A getWidth()
 *
 * A setExtension()
 * A set_file_upload_folder()
 * A set_files_of_members()
 * A setHeight()
 * A setMimeType()
 * A setName()
 * A setSite()
 * A setSize()
 * A setSourceOriginal()
 * A setSourcePreview()
 * A setType()
 * A setUrlKey()
 * A setWidth()
 *
 */

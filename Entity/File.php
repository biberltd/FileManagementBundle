<?php
/**
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        22.12.2015
 */
namespace BiberLtd\Bundle\FileManagementBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use \BiberLtd\Bundle\CoreBundle\CoreLocalizableEntity;
/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="file",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={
 *         @ORM\Index(name="idxNFileMimeType", columns={"mime_type"}),
 *         @ORM\Index(name="idxNFileExtension", columns={"extension"}),
 *         @ORM\Index(name="idxNFileDimension", columns={"width","height"}),
 *         @ORM\Index(name="idxNFileDateAdded", columns={"date_added"}),
 *         @ORM\Index(name="idxNFileDateUpdated", columns={"date_updated"}),
 *         @ORM\Index(name="idxNFileDateRemoved", columns={"date_removed"})
 *     },
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idxUFileId", columns={"id"})}
 * )
 */
class File extends CoreLocalizableEntity
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", length=15)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=165, nullable=false)
     * @var string
     */
    private $name;

    /** 
     * @ORM\Column(type="string", length=255, nullable=false)
     * @var string
     */
    private $url_key;

    /** 
     * @ORM\Column(type="text", nullable=false)
     * @var string
     */
    private $source_original;

    /** 
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $source_preview;

    /** 
     * @ORM\Column(type="string", length=1, nullable=false, options={"default":"i"})
     * @var string
     */
    private $type;

    /** 
     * @ORM\Column(type="integer", length=5, nullable=true)
     * @var int
     */
    private $width;

    /** 
     * @ORM\Column(type="integer", length=5, nullable=true)
     * @var int
     */
    private $height;

    /** 
     * @ORM\Column(type="decimal", unique=true, length=5, nullable=false, options={"default":0})
     * @var float
     */
    private $size;

    /** 
     * @ORM\Column(type="string", length=45, nullable=false)
     * @var string
     */
    private $mime_type;

    /** 
     * @ORM\Column(type="string", length=6, nullable=false)
     * @var string
     */
    private $extension;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $tags;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $exif;

	/**
	 * @ORM\Column(type="datetime", nullable=false)
	 * @var \DateTime
	 */
	public $date_added;

	/**
	 * @ORM\Column(type="datetime", nullable=false)
	 * @var \DateTime
	 */
	public $date_updated;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @var \DateTime
	 */
	public $date_removed;

    /**
     * @ORM\OneToMany(
     *     targetEntity="BiberLtd\Bundle\FileManagementBundle\Entity\FileLocalization",
     *     mappedBy="file",
     *     cascade={"persist"}
     * )
     * @var array
     */
    protected $localizations;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="CASCADE")
     * @var \BiberLtd\Bundle\SiteManagementBundle\Entity\Site
     */
    private $site;

	/**
	 * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\FileManagementBundle\Entity\FileUploadFolder", inversedBy="files")
	 * @ORM\JoinColumn(name="folder", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 * @var \BiberLtd\Bundle\FileManagementBundle\Entity\FileUploadFolder
	 */
    private $folder;

	/**
	 * @return mixed
	 */
    public function getId(){
        return $this->id;
    }

	/**
	 * @param string $extension
	 *
	 * @return $this
	 */
    public function setExtension(string $extension) {
        if(!$this->setModified('extension', $extension)->isModified()) {
            return $this;
        }
		$this->extension = $extension;
		return $this;
    }

	/**
	 * @return string
	 */
    public function getExtension() {
        return $this->extension;
    }


    /**
     * @param \BiberLtd\Bundle\FileManagementBundle\Entity\FileUploadFolder $folder
     *
     * @return $this
     */
    public function setFolder(FileUploadFolder $folder) {
        if(!$this->setModified('folder', $folder)->isModified()) {
            return $this;
        }
        $this->folder = $folder;
        return $this;
    }

    /**
     * @return \BiberLtd\Bundle\FileManagementBundle\Entity\FileUploadFolder
     */
    public function getFolder() {
        return $this->folder;
    }

	/**
	 * @param int $height
	 *
	 * @return $this
	 */
    public function setHeight(int $height) {
        if(!$this->setModified('height', $height)->isModified()) {
            return $this;
        }
		$this->height = $height;
		return $this;
    }

	/**
	 * @return int
	 */
    public function getHeight() {
        return $this->height;
    }

	/**
	 * @param string $mime_type
	 *
	 * @return $this
	 */
    public function setMimeType(string $mime_type) {
        if(!$this->setModified('mime_type', $mime_type)->isModified()) {
            return $this;
        }
		$this->mime_type = $mime_type;
		return $this;
    }

	/**
	 * @return string
	 */
    public function getMimeType() {
        return $this->mime_type;
    }

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
    public function setName(string $name) {
        if(!$this->setModified('name', $name)->isModified()) {
            return $this;
        }
		$this->name = $name;
		return $this;
    }

	/**
	 * @return string
	 */
    public function getName() {
        return $this->name;
    }

	/**
	 * @param \BiberLtd\Bundle\SiteManagementBundle\Entity\Site $site
	 *
	 * @return $this
	 */
    public function setSite(\BiberLtd\Bundle\SiteManagementBundle\Entity\Site $site) {
        if(!$this->setModified('site', $site)->isModified()) {
            return $this;
        }
		$this->site = $site;
		return $this;
    }

	/**
	 * @return \BiberLtd\Bundle\SiteManagementBundle\Entity\Site
	 */
    public function getSite() {
        return $this->site;
    }

	/**
	 * @param float $size
	 *
	 * @return $this
	 */
    public function setSize(float $size) {
        if(!$this->setModified('size', $size)->isModified()) {
            return $this;
        }
		$this->size = $size;
		return $this;
    }

	/**
	 * @return float
	 */
    public function getSize() {
        return $this->size;
    }

	/**
	 * @param string $source_original
	 *
	 * @return $this
	 */
    public function setSourceOriginal(string $source_original) {
        if(!$this->setModified('source_original', $source_original)->isModified()) {
            return $this;
        }
		$this->source_original = $source_original;
		return $this;
    }

	/**
	 * @return string
	 */
    public function getSourceOriginal() {
        return $this->source_original;
    }

	/**
	 * @param string $source_preview
	 *
	 * @return $this
	 */
    public function setSourcePreview(string $source_preview) {
        if(!$this->setModified('source_preview', $source_preview)->isModified()) {
            return $this;
        }
		$this->source_preview = $source_preview;
		return $this;
    }

	/**
	 * @return string
	 */
    public function getSourcePreview() {
        return $this->source_preview;
    }

	/**
	 * @param string $type
	 *
	 * @return $this
	 */
    public function setType(string $type) {
        if(!$this->setModified('type', $type)->isModified()) {
            return $this;
        }
		$this->type = $type;
		return $this;
    }

	/**
	 * @return string
	 */
    public function getType() {
        return $this->type;
    }

	/**
	 * @param string $url_key
	 *
	 * @return $this
	 */
    public function setUrlKey(string $url_key) {
        if(!$this->setModified('url_key', $url_key)->isModified()) {
            return $this;
        }
		$this->url_key = $url_key;
		return $this;
    }

	/**
	 * @return string
	 */
    public function getUrlKey() {
        return $this->url_key;
    }

	/**
	 * @param int $width
	 *
	 * @return $this
	 */
    public function setWidth(int $width) {
        if(!$this->setModified('width', $width)->isModified()) {
            return $this;
        }
		$this->width = $width;
		return $this;
    }

    /**
     * @name            getWidth ()
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
	 * @param string $exif
	 *
	 * @return $this
	 */
    public function setExif(string $exif) {
        if($this->setModified('exif', $exif)->isModified()) {
            $this->exif = $exif;
        }

        return $this;
    }

	/**
	 * @return string
	 */
    public function getExif() {
        return $this->exif;
    }

	/**
	 * @param array $tags
	 *
	 * @return $this
	 */
    public function setTags(array $tags) {
        if($this->setModified('tags', $tags)->isModified()) {
            $this->tags = $tags;
        }

        return $this;
    }

	/**
	 * @return string
	 */
    public function getTags() {
        return $this->tags;
    }
}
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
use BiberLtd\Bundle\CoreBundle\CoreEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="file_upload_folder",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={
 *         @ORM\Index(name="idxNFileUploadFolderDateAdded", columns={"date_added"}),
 *         @ORM\Index(name="idxNFileUploadFolderDateUpdated", columns={"date_updated"}),
 *         @ORM\Index(name="idxNFileUploadFolderDateRemoved", columns={"date_removed"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="idxUFileUploadFolderId", columns={"id"}),
 *         @ORM\UniqueConstraint(name="idxUFileUploadFolderUrlKey", columns={"url_key","site"})
 *     }
 * )
 */
class FileUploadFolder extends CoreEntity
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", length=10)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /** 
     * @ORM\Column(type="string", length=155, nullable=false)
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
    private $path_absolute;

    /** 
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $url;

    /** 
     * @ORM\Column(type="string", length=1, nullable=false, options={"default":"i"})
     * @var string
     */
    private $type;

    /** 
     * @ORM\Column(type="decimal", length=5, nullable=true)
     * @var float
     */
    private $allowed_max_size;

    /** 
     * @ORM\Column(type="decimal", length=5, nullable=true)
     * @var float
     */
    private $allowed_min_size;

    /** 
     * @ORM\Column(type="integer", length=5, nullable=true)
     * @var float
     */
    private $allowed_max_width;

    /** 
     * @ORM\Column(type="integer", length=5, nullable=true)
     * @var float
     */
    private $allowed_min_width;

    /** 
     * @ORM\Column(type="integer", length=5, nullable=true)
     * @var float
     */
    private $allowed_max_height;

    /** 
     * @ORM\Column(type="integer", length=5, nullable=true)
     * @var float
     */
    private $allowed_min_height;

    /** 
     * @ORM\Column(type="integer", length=10, nullable=false, options={"default":0})
     * @var int
     */
    private $count_files;

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
     * @ORM\OneToMany(targetEntity="BiberLtd\Bundle\FileManagementBundle\Entity\File", mappedBy="folder")
     * @var array
     */
    private $files;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @var \BiberLtd\Bundle\SiteManagementBundle\Entity\Site
     */
    private $site;

	/**
	 * @return mixed
	 */
    public function getId(){
        return $this->id;
    }

	/**
	 * @param float $allowed_max_height
	 *
	 * @return $this
	 */
    public function setAllowedMaxHeight(\float $allowed_max_height) {
        if(!$this->setModified('allowed_max_height', $allowed_max_height)->isModified()) {
            return $this;
        }
		$this->allowed_max_height = $allowed_max_height;
		return $this;
    }

	/**
	 * @return float
	 */
    public function getAllowedMaxHeight() {
        return $this->allowed_max_height;
    }

	/**
	 * @param float $allowed_max_size
	 *
	 * @return $this
	 */
    public function setAllowedMaxSize(\float $allowed_max_size) {
        if(!$this->setModified('allowed_max_size', $allowed_max_size)->isModified()) {
            return $this;
        }
		$this->allowed_max_size = $allowed_max_size;
		return $this;
    }

	/**
	 * @return float
	 */
    public function getAllowedMaxSize() {
        return $this->allowed_max_size;
    }

	/**
	 * @param float $allowed_max_width
	 *
	 * @return $this
	 */
    public function setAllowedMaxWidth(\float $allowed_max_width) {
        if(!$this->setModified('allowed_max_width', $allowed_max_width)->isModified()) {
            return $this;
        }
		$this->allowed_max_width = $allowed_max_width;
		return $this;
    }

	/**
	 * @return float
	 */
    public function getAllowedMaxWidth() {
        return $this->allowed_max_width;
    }

	/**
	 * @param float $allowed_min_height
	 *
	 * @return $this
	 */
    public function setAllowedMinHeight(\float $allowed_min_height) {
        if(!$this->setModified('allowed_min_height', $allowed_min_height)->isModified()) {
            return $this;
        }
		$this->allowed_min_height = $allowed_min_height;
		return $this;
    }

	/**
	 * @return float
	 */
    public function getAllowedMinHeight() {
        return $this->allowed_min_height;
    }

	/**
	 * @param float $allowed_min_size
	 *
	 * @return $this
	 */
    public function setAllowedMinSize(\float $allowed_min_size) {
        if(!$this->setModified('allowed_min_size', $allowed_min_size)->isModified()) {
            return $this;
        }
		$this->allowed_min_size = $allowed_min_size;
		return $this;
    }

	/**
	 * @return float
	 */
    public function getAllowedMinSize() {
        return $this->allowed_min_size;
    }

	/**
	 * @param float $allowed_min_width
	 *
	 * @return $this
	 */
    public function setAllowedMinWidth(\float $allowed_min_width) {
        if(!$this->setModified('allowed_min_width', $allowed_min_width)->isModified()) {
            return $this;
        }
		$this->allowed_min_width = $allowed_min_width;
		return $this;
    }

	/**
	 * @return float
	 */
    public function getAllowedMinWidth() {
        return $this->allowed_min_width;
    }

	/**
	 * @param int $count_files
	 *
	 * @return $this
	 */
    public function setCountFiles(\integer $count_files) {
        if(!$this->setModified('count_files', $count_files)->isModified()) {
            return $this;
        }
		$this->count_files = $count_files;
		return $this;
    }

	/**
	 * @return int
	 */
    public function getCountFiles() {
        return $this->count_files;
    }

	/**
	 * @param array $files
	 *
	 * @return $this
	 */
    public function setFiles(array $files) {
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
	 * @param string $name
	 *
	 * @return $this
	 */
    public function setName(\string $name) {
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
	 * @param string $path_absolute
	 *
	 * @return $this
	 */
    public function setPathAbsolute(\string $path_absolute) {
        if(!$this->setModified('path_absolute', $path_absolute)->isModified()) {
            return $this;
        }
		$this->path_absolute = $path_absolute;
		return $this;
    }

	/**
	 * @return string
	 */
    public function getPathAbsolute() {
        return $this->path_absolute;
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
	 * @param string $type
	 *
	 * @return $this
	 */
    public function setType(\string $type) {
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
	 * @param $url
	 *
	 * @return $this
	 */
    public function setUrl($url) {
        if(!$this->setModified('url', $url)->isModified()) {
            return $this;
        }
		$this->url = $url;
		return $this;
    }

	/**
	 * @return string
	 */
    public function getUrl() {
        return $this->url;
    }

	/**
	 * @param string $url_key
	 *
	 * @return $this
	 */
    public function setUrlKey(\string $url_key) {
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
}
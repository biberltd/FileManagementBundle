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
use BiberLtd\Bundle\CoreBundle\CoreEntity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="file_localization",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idxUFileLocalization", columns={"file","language"})}
 * )
 */
class FileLocalization extends CoreEntity
{
    /**
     * @ORM\Column(type="string", length=155, nullable=false)
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    private $description;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\FileManagementBundle\Entity\File", inversedBy="localizations")
     * @ORM\JoinColumn(name="file", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @var \BiberLtd\Bundle\FileManagementBundle\Entity\File
     */
    private $file;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language")
     * @ORM\JoinColumn(name="language", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @var \BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language
     */
    private $language;

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription(string $description) {
        if($this->setModified('description', $description)->isModified()) {
            $this->description = $description;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param \BiberLtd\Bundle\FileManagementBundle\Entity\File $file
     *
     * @return $this
     */
    public function setFile(\BiberLtd\Bundle\FileManagementBundle\Entity\File $file) {
        if($this->setModified('file', $file)->isModified()) {
            $this->file = $file;
        }

        return $this;
    }

    /**
     * @return \BiberLtd\Bundle\FileManagementBundle\Entity\File
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * @param \BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language $language
     *
     * @return $this
     */
    public function setLanguage(\BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language $language) {
        if($this->setModified('language', $language)->isModified()) {
            $this->language = $language;
        }

        return $this;
    }

    /**
     * @return \BiberLtd\Bundle\MultiLanguageSupportBundle\Entity\Language
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle(string $title) {
        if($this->setModified('title', $title)->isModified()) {
            $this->title = $title;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }
}
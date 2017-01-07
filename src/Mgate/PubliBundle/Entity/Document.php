<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\PubliBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

use Mgate\PersonneBundle\Entity\Personne;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Mgate\PubliBundle\Entity\DocumentRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="RelatedDocument", inversedBy="document", cascade={"all"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $relation;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="uptime", type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $uptime;

    /**
     * @ORM\ManyToOne(targetEntity="Mgate\PersonneBundle\Entity\Personne", cascade={"persist"})
     * @ORM\JoinColumn(name="author_personne_id", referencedColumnName="id", nullable=true)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @var UploadedFile
     * @Assert\File(maxSize="6000000")
     */
    private $file;

    /**
     * @var string
     * @Assert\NotBlank
     * Folder where all uploaded documents will be stored, without trailing slash.
     */
    private $rootDir;

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    protected function getRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return $this->rootDir;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return $this->rootDir.'/uploads';
    }


    /**
     * @param $rootDir string folder where documents should be stored without trailing slash.
     */
    public function setRootDir($rootDir){
        $this->rootDir = $rootDir.'/..';
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'documents';
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->path = $filename.'.'.$this->file->guessExtension();
            $this->size = filesize($this->file);
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        // moving file into /data
        $this->file->move($this->getUploadRootDir(), $this->path);
        // creating symlink to acces file from web/...
        symlink($this->getUploadRootDir().'/'.$this->path, $this->getWebPath());
        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if($this->rootDir !== null) {
            if ($file = $this->getWebPath()) {
                unlink($file);
            }
            if ($file = $this->getAbsolutePath()) {
                unlink($file);
            }
        }
        else{
            throw  new \Exception('rootDir non défini lors de la suppression du document. Définissez le via setRootDir avant toute manipulation.');
        }

    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Document
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get uptime.
     *
     * @return \DateTime
     */
    public function getUptime()
    {
        return $this->uptime;
    }

    /**
     * Set path.
     *
     * @param string $path
     *
     * @return Document
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Set path.
     *
     * @param string $junior['id']
     *
     * @return Document
     */
    public function setSubdirectory($path)
    {
        $this->subdirectory = $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set relation.
     *
     * @param RelatedDocument $relation
     *
     * @return Document
     */
    public function setRelation(RelatedDocument $relation = null)
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * Get relation.
     *
     * @return RelatedDocument
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Set author.
     *
     * @param Personne $author
     *
     * @return Document
     */
    public function setAuthor(Personne $author = null)
    {
        $this->author = $author;

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
        $this->size = filesize($file);

        return $this;
    }

    /**
     * Get author.
     *
     * @return Personne
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Get size.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }
}
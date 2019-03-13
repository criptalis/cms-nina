<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Categorie
 *
 * @ORM\Entity(repositoryClass="App\Repository\CategorieRepository")
 */
class Categorie
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     * @Assert\NotBlank
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="categorieParent", type="string", length=255, nullable=true)
     */
    private $categorieParent = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TypeCategorie", inversedBy="categories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $typeCategorie;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Page", mappedBy="categories")
     * @ORM\OrderBy({"datePublication" = "DESC"})
     */
    private $pages;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Langue")
     * @ORM\JoinColumn(nullable=false)
     */
    private $langue;

    public function __toString()
    {
        if($this->getNom()){
            return $this->getNom();
        }

        return 'Catégorie';
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Categorie
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Categorie
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Categorie
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set categorieParent
     *
     * @param string $categorieParent
     *
     * @return Categorie
     */
    public function setCategorieParent($categorieParent)
    {
        $this->categorieParent = $categorieParent;

        return $this;
    }

    /**
     * Get categorieParent
     *
     * @return string
     */
    public function getCategorieParent()
    {
        return $this->categorieParent;
    }

    /**
     * Set typeCategorie
     *
     * @param \App\Entity\TypeCategorie $typeCategorie
     *
     * @return Categorie
     */
    public function setTypeCategorie(\App\Entity\TypeCategorie $typeCategorie = null)
    {
        $this->typeCategorie = $typeCategorie;

        return $this;
    }

    /**
     * Get typeCategorie
     *
     * @return \App\Entity\TypeCategorie
     */
    public function getTypeCategorie()
    {
        return $this->typeCategorie;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pages = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Add page
     *
     * @param \App\Entity\Page $page
     *
     * @return Categorie
     */
    public function addPage(\App\Entity\Page $page)
    {
        $this->pages[] = $page;

        return $this;
    }

    /**
     * Remove page
     *
     * @param \App\Entity\Page $page
     */
    public function removePage(\App\Entity\Page $page)
    {
        $this->pages->removeElement($page);
    }

    /**
     * Get pages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPages()
    {
        return $this->pages;
    }

    public function getLangue(): ?Langue
    {
        return $this->langue;
    }

    public function setLangue(?Langue $langue): self
    {
        $this->langue = $langue;

        return $this;
    }
}

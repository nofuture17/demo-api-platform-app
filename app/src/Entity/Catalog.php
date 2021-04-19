<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CatalogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=CatalogRepository::class)
 */
class Catalog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $key;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=CatalogField::class, mappedBy="catalog", orphanRemoval=true)
     */
    private $catalogFields;

    /**
     * @ORM\OneToMany(targetEntity=CatalogRecord::class, mappedBy="catalog", orphanRemoval=true)
     */
    private $catalogRecords;

    public function __construct()
    {
        $this->catalogFields = new ArrayCollection();
        $this->catalogRecords = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|CatalogField[]
     */
    public function getCatalogFields(): Collection
    {
        return $this->catalogFields;
    }

    public function addCatalogField(CatalogField $catalogField): self
    {
        if (!$this->catalogFields->contains($catalogField)) {
            $this->catalogFields[] = $catalogField;
            $catalogField->setCatalog($this);
        }

        return $this;
    }

    public function removeCatalogField(CatalogField $catalogField): self
    {
        if ($this->catalogFields->removeElement($catalogField)) {
            // set the owning side to null (unless already changed)
            if ($catalogField->getCatalog() === $this) {
                $catalogField->setCatalog(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CatalogRecord[]
     */
    public function getCatalogRecords(): Collection
    {
        return $this->catalogRecords;
    }

    public function addCatalogRecord(CatalogRecord $catalogRecord): self
    {
        if (!$this->catalogRecords->contains($catalogRecord)) {
            $this->catalogRecords[] = $catalogRecord;
            $catalogRecord->setCatalog($this);
        }

        return $this;
    }

    public function removeCatalogRecord(CatalogRecord $catalogRecord): self
    {
        if ($this->catalogRecords->removeElement($catalogRecord)) {
            // set the owning side to null (unless already changed)
            if ($catalogRecord->getCatalog() === $this) {
                $catalogRecord->setCatalog(null);
            }
        }

        return $this;
    }
}

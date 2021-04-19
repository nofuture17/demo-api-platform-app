<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CatalogRecordRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=CatalogRecordRepository::class)
 */
class CatalogRecord
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Catalog::class, inversedBy="catalogRecords")
     * @ORM\JoinColumn(nullable=false)
     */
    private $catalog;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToOne(targetEntity=CatalogRecordAttributes::class, mappedBy="record", orphanRemoval=true, fetch="EAGER")
     */
    private $attributes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCatalog(): ?Catalog
    {
        return $this->catalog;
    }

    public function setCatalog(?Catalog $catalog): self
    {
        $this->catalog = $catalog;

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

    public function getAttributes(): CatalogRecordAttributes
    {
        return $this->attributes;
    }

    public function setAttributes(CatalogRecordAttributes $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }
}

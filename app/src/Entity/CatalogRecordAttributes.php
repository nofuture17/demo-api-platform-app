<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class CatalogRecordAttributes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=CatalogRecord::class, inversedBy="attributes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $record;

    public $values = [];

    public function __set(string $name, $value): void
    {
        $this->values[$name] = $value;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 * @codeCoverageIgnore
 */
class ImageFile
{
    public const TYPE_ORIGINAL = 'original';
    public const TYPE_THUMB_SMALL = 'thumbSmall';
    public const TYPE_THUMB_BIG = 'thumbBig';
    public const GROUP_READ = 'imageFile:read';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="integer")
     */
    #[Groups([self::GROUP_READ])]
    private int $height = 0;

    /**
     * @ORM\Column(type="integer")
     */
    #[Groups([self::GROUP_READ])]
    private int $width = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups([self::GROUP_READ])]
    private string $type = self::TYPE_ORIGINAL;

    /**
     * @ORM\ManyToOne(targetEntity=Image::class, inversedBy="files")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Image $image;

    #[Pure]
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    #[Pure]
    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    #[Pure]
    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setAsOriginal(): self
    {
        $this->type = self::TYPE_ORIGINAL;

        return $this;
    }

    public function setAsThumbSmall(): self
    {
        $this->type = self::TYPE_THUMB_SMALL;

        return $this;
    }

    public function setAsThumbBig(): self
    {
        $this->type = self::TYPE_THUMB_BIG;

        return $this;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    #[Pure]
    #[Groups([self::GROUP_READ])]
    public function getUrl(): string
    {
        $thumpParam = $this->isOriginal() ? '' : "?thumb={$this->type}";

        return Image::PATH_DOWNLOAD."/{$this->image->getId()}$thumpParam";
    }

    private function isOriginal(): bool
    {
        return self::TYPE_ORIGINAL === $this->type;
    }
}

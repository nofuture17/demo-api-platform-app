<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\DeleteController;
use App\Controller\DownloadController;
use App\Controller\GetFailedController;
use App\Controller\GetSuccessController;
use App\Controller\UploadDataController;
use App\Controller\UploadFileController;
use App\DTO\FileDataInput;
use App\Repository\ImageRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[ApiResource(collectionOperations: [
    'get',
    'getSuccess' => [
        'method' => 'GET',
        'path' => '/images/success',
        'controller' => GetSuccessController::class,
        'normalization_context' => [
            AbstractNormalizer::GROUPS => [self::GROUP_SUCCESS, ImageFile::GROUP_READ],
        ],
    ],
    'getFailed' => [
        'method' => 'GET',
        'path' => '/images/failed',
        'controller' => GetFailedController::class,
        'normalization_context' => [
            AbstractNormalizer::GROUPS => [self::GROUP_ERROR],
        ],
    ],
    'uploadFile' => [
        'method' => 'POST',
        'path' => '/images/upload/file',
        'controller' => UploadFileController::class,
        'openapi_context' => [
            'summary' => 'Create image from multipart/form-data',
            'responses' => self::UPLOAD_FILE_RESPONSES,
            'requestBody' => [
                'content' => [
                    'multipart/form-data' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                self::FILES_REQUEST_PROPERTY => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'uploadData' => [
        'method' => self::METHOD_UPLOAD_DATA,
        'path' => self::PATH_UPLOAD_DATA,
        'controller' => UploadDataController::class,
        'input' => FileDataInput::class,
        'openapi_context' => [
            'summary' => 'Create image from base64 or url content',
            'responses' => self::UPLOAD_FILE_RESPONSES,
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/FileDataInput.jsonld',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
], itemOperations: [
    'get',
    'download' => [
        'method' => 'GET',
        'path' => self::PATH_DOWNLOAD.'/{id}',
        'controller' => DownloadController::class,
        'openapi_context' => [
            'summary' => 'Create image from multipart/form-data',
            'parameters' => [
                [
                    'name' => self::THUMB_REQUEST_PROPERTY,
                    'in' => 'query',
                    'description' => 'thumb type',
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
            ],
            'responses' => [
                200 => [
                    'description' => 'File content',
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'string',
                                'format' => 'binary',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'delete' => [
        'controller' => DeleteController::class,
    ],
], attributes: [
    'pagination_items_per_page' => self::ITEMS_PER_PAGE,
])]
/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 * @codeCoverageIgnore
 */
class Image
{
    public const ITEMS_PER_PAGE = 30;
    public const PATH_DOWNLOAD = '/images/download';
    public const PATH_UPLOAD_DATA = '/images/upload/data';
    public const METHOD_UPLOAD_DATA = 'POST';
    public const GROUP_ERROR = 'image:error';
    public const GROUP_SUCCESS = 'image:success';

    private const UPLOAD_FILE_RESPONSES = [
        201 => [
            'description' => 'Images',
            'content' => [
                'application/json' => self::UPLOAD_FILE_201_RESPONSE_SCHEMA,
                'application/id+json' => self::UPLOAD_FILE_201_RESPONSE_SCHEMA,
                'text/html' => self::UPLOAD_FILE_201_RESPONSE_SCHEMA,
            ],
        ],
    ];

    private const UPLOAD_FILE_201_RESPONSE_SCHEMA = [
        'schema' => [
            'type' => 'array',
            'items' => [
                '$ref' => '#/components/schemas/Image.jsonld',
            ],
        ],
    ];
    public const FILES_REQUEST_PROPERTY = 'files';
    public const THUMB_REQUEST_PROPERTY = 'thumb';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups([self::GROUP_ERROR, self::GROUP_SUCCESS])]
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $extension = '';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({Image::GROUP_ERROR})
     */
    #[Groups([self::GROUP_ERROR])]
    private ?string $error = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=ImageFile::class, mappedBy="image", orphanRemoval=true, fetch="EAGER", cascade={"persist"})
     *
     * @var Collection<int, \App\Entity\ImageFile>
     */
    #[Groups([self::GROUP_SUCCESS])]
    private Collection $files;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->files = new ArrayCollection();
    }

    #[Pure]
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

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
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    #[Pure]
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    #[Pure]
    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): self
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @Groups({Image::GROUP_SUCCESS})
     */
    #[Pure]
    public function getFullName(): ?string
    {
        return !empty($this->getExtension()) ? "{$this->getName()}.{$this->getExtension()}" : $this->getName();
    }

    /**
     * @return Collection<int, ImageFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(ImageFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
            $file->setImage($this);
        }

        return $this;
    }

    public function removeFile(ImageFile $file): self
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getImage() === $this) {
                $file->setImage(null);
            }
        }

        return $this;
    }
}

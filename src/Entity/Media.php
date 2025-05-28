<?php

namespace App\Entity;

use App\Contract\OwnableInterface;
use App\Repository\MediaRepository;
use App\Trait\OwnableEntityTrait;
use App\Traits\TimestampableTrait;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'herds'),
])]
class Media implements OwnableInterface
{
    use OwnableEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['media', 'healthcare'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['media', 'healthcare'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(
        max: 255,
    )]
    private ?string $realName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['media', 'healthcare'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(
        max: 255,
    )]
    private ?string $realPath = null;

    #[ORM\Column(length: 255)]
    #[Groups(['media', 'healthcare'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(
        max: 255,
    )]
    private ?string $publicPath = null;

    #[ORM\Column(length: 255)]
    #[Groups(['media', 'healthcare'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(
        max: 255,
    )]
    private ?string $mime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['media', 'healthcare'])]
    private ?DateTimeInterface $uploadedAt = null;

    #[Vich\UploadableField('media_file', fileNameProperty: 'realPath', mimeType: 'mime')]
    private ?File $file = null;

    #[ORM\ManyToOne(targetEntity: Healthcare::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Healthcare $healthcare = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRealName(): ?string
    {
        return $this->realName;
    }

    public function setRealName(string $realName): static
    {
        $this->realName = $realName;

        return $this;
    }

    public function getRealPath(): ?string
    {
        return $this->realPath;
    }

    public function setRealPath(string $realPath): static
    {
        $this->realPath = $realPath;

        return $this;
    }

    public function getPublicPath(): ?string
    {
        return $this->publicPath;
    }

    public function setPublicPath(string $publicPath): static
    {
        $this->publicPath = $publicPath;

        return $this;
    }

    public function getMime(): ?string
    {
        return $this->mime;
    }

    public function setMime(string $mime): static
    {
        $this->mime = $mime;

        return $this;
    }

    public function getUploadedAt(): ?DateTimeInterface
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(DateTimeInterface $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getHealthcare(): ?Healthcare
    {
        return $this->healthcare;
    }

    public function setHealthcare(?Healthcare $healthcare): static
    {
        $this->healthcare = $healthcare;

        return $this;
    }
}

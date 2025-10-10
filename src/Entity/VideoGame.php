<?php

namespace App\Entity;

use App\Repository\VideoGameRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VideoGameRepository::class)]

class VideoGame
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(["videoGame:write"])]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    #[Groups(["videogame:read", "videoGame:write"])]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(["videogame:read", "videoGame:write"])]
    #[Assert\NotNull(message: "La date de sortie est obligatoire.")]
    #[Assert\Type(DateTime::class)]
    private ?DateTime $release_date = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["videogame:read", "videoGame:write"])]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $updated_at = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'videoGames', cascade: ['persist', 'remove'])]
    #[Groups(["videogame:read", "videoGame:write"])]
    #[Assert\Count(
        min: 1,
        minMessage: "Le jeu doit appartenir à au moins une catégorie."
    )]
    private Collection $category_id;

    #[ORM\ManyToOne(inversedBy: 'videoGames')]
    #[Assert\NotNull(message: "L'éditeur est obligatoire.")]
    #[Groups(["videogame:read", "videoGame:write"])]
    private ?Editor $editor_id = null;

    public function __construct()
    {
        $this->category_id = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getReleaseDate(): ?DateTime
    {
        return $this->release_date;
    }

    public function setReleaseDate(DateTime $release_date): static
    {
        $this->release_date = $release_date;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategoryId(): Collection
    {
        return $this->category_id;
    }

    public function addCategoryId(Category $categoryId): static
    {
        if (!$this->category_id->contains($categoryId)) {
            $this->category_id->add($categoryId);
        }

        return $this;
    }

    public function removeCategoryId(Category $categoryId): static
    {
        $this->category_id->removeElement($categoryId);

        return $this;
    }

    public function getEditorId(): ?Editor
    {
        return $this->editor_id;
    }

    public function setEditorId(?Editor $editor_id): static
    {
        $this->editor_id = $editor_id;

        return $this;
    }
}

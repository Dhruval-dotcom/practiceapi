<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\TreasureRepository;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TreasureRepository::class)]
#[ApiResource(
    shortName: 'Treas',
    description: 'An amazing treasure',
    operations: [
        new Get(
            normalizationContext: [
                'groups' => ['treasure:read','treasure:item:get']
            ]
        ),
        new GetCollection(),
        new Put(),
        new Post(),
        new Patch(),
        new Delete() 
    ],
    normalizationContext: [
        'groups' => ['treasure:read']
    ],
    denormalizationContext: [
        'groups' => ['treasure:write']
    ],
    paginationItemsPerPage: 10,
    formats: [
        'jsonld',
        'json',
        'html',
        'jsonhal',
        'csv' => 'text/csv'
    ]
)]
#[ApiFilter(PropertyFilter::class)]
#[ApiFilter(
    SearchFilter::class, 
    properties: [
        'owner.username' => 'partial'
    ]
)]
#[ApiResource(
    shortName: 'Treas',
    uriTemplate: 'users/{user_id}/treasures.{_format}',
    operations: [
        new GetCollection()
    ],
    uriVariables: [
        'user_id' => new Link(
            toProperty: 'owner',
            fromClass: User::class
        )
    ],
    normalizationContext: [
        'groups' => ['treasure:read']
    ]
)]
class Treasure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['treasure:read','treasure:write','user:read','user:write'])]
    #[ApiFilter(SearchFilter::class, strategy: 'partial')]
    #[Assert\NotBlank]
    #[Assert\Length(
        min:2,
        max:50,
        minMessage: 'Describe your name more than 2 char',
        maxMessage: 'Less than 50 chars needed'
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['treasure:read','treasure:write','user:write'])]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['treasure:read','treasure:write','user:read','user:write'])]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $value = 0;

    #[ORM\Column]
    #[Groups(['treasure:read','treasure:write'])]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(900)]
    private ?int $coolfactor = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['treasure:read'])]
    #[ApiFilter(BooleanFilter::class)]
    private ?bool $isPublished = false;

    #[ORM\ManyToOne(inversedBy: 'treasures')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['treasure:read','treasure:write'])]
    #[Assert\Valid]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    private ?User $owner = null;

    public function __construct(string $name = null)
    {
        $this->name = $name;
        $this->createdAt = new \DateTimeImmutable();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    // public function setName(string $name): static
    // {
    //     $this->name = $name;

    //     return $this;
    // }

    public function getDescription(): ?string
    {
        return $this->description;
    }
    #[Groups(['treasure:read'])]
    public function getshortDescription(): ?string
    {
        return substr($this->description,0,40).'...';
    }
    public function setDescription(string $description): static
    {
        $this->description = nl2br($description);

        return $this;
    }
    #[Groups(['treasure:write','user:write'])]
    #[SerializedName('description')]
    public function setTextDescription(string $description): static
    {
        $this->description = nl2br($description);

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getCoolfactor(): ?int
    {
        return $this->coolfactor;
    }

    public function setCoolfactor(int $coolfactor): static
    {
        $this->coolfactor = $coolfactor;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTimeImmutable $dateTimeImmutable): static
    {
        $this->createdAt = $dateTimeImmutable;
        return $this;
    }
    #[Groups('treasure:read')]
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->createdAt)->diffForHumans();
    }
    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}

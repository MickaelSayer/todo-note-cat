<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TaskRepository;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "update task checked",
 *      href = @Hateoas\Route(
 *          "api_setTaskChecked",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      attributes = {
 *          "title" = "task update checked",
 *          "verbe HTTP" = "PATCH",
 *          "route_name" = "api_setTaskChecked",
 *          "additional_information" = {
 *              "authentication" = "Include JWT Token in the Authorization header."
 *          }
 *      },
 *      exclusion = @Hateoas\Exclusion(
 *          groups={"note:read", "note:update"},
 *          excludeIf = "expr(not (is_granted('ROLE_USER')))"
 *      )
 * )
 */
#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    /**
     * @Groups({"note:read", "task:checked", "note:update"})
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Note $note = null;

    /**
     * @Groups({"note:read", "note:update"})
     */
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "La/les descriptions sont obligatoire.")]
    #[Assert\Length(
        max: 100,
        maxMessage: 'La/les descriptions sont trop longues.',
    )]
    private ?string $description = null;

    /**
     * @Groups({"note:read", "task:checked", "note:update"})
     */
    #[ORM\Column]
    private ?bool $checked = null;

    /**
     * @Groups({"note:read"})
     */
    #[ORM\Column]
    private ?string $created_at = null;

    public function __construct()
    {
        $this->checked = false;
        $this->created_at = (new DateTimeImmutable())->format('Y-m-d');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getNote(): ?Note
    {
        return $this->note;
    }

    public function setNote(?Note $note): static
    {
        $this->note = $note;

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

    public function isChecked(): ?bool
    {
        return $this->checked;
    }

    public function setChecked(bool $checked): static
    {
        $this->checked = $checked;

        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}

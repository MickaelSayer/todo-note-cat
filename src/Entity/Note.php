<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\NoteRepository;
use JMS\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "api_deleteNote",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      attributes = {
 *          "title" = "Delete note",
 *          "verbe HTTP" = "DELETE",
 *          "route_name" = "api_deleteNote",
 *          "additional_information" = {
 *              "authentication" = "Include JWT Token in the Authorization header."
 *          }
 *      },
 *      exclusion = @Hateoas\Exclusion(
 *          groups={"note:read", "note:update"},
 *          excludeIf = "expr(not (is_granted('ROLE_USER')))"
 *      )
 * )
 *
 * @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "api_updateNote",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      attributes = {
 *          "title" = "Note update",
 *          "verbe HTTP" = "PATCH",
 *          "route_name" = "api_updateNote",
 *          "additional_information" = {
 *              "authentication" = "Include JWT Token in the Authorization header.",
 *              "body" = "Include title and tasks description JSON in the body RAW."
 *          }
 *      },
 *      exclusion = @Hateoas\Exclusion(
 *          groups={"note:read"},
 *          excludeIf = "expr(not (is_granted('ROLE_USER')))"
 *      )
 * )
 */
#[ORM\Entity(repositoryClass: NoteRepository::class)]
class Note
{
    /**
     * @Groups({"note:read", "note:update"})
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @Groups({"note:read"})
     */
    #[ORM\ManyToOne(inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @Groups({"note:read", "note:update"})
     */
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
        max: 100,
        maxMessage: 'Le titre est trop long.',
    )]
    private ?string $title = null;

    /**
     * @Groups({"note:read"})
     */
    #[ORM\Column]
    private ?string $created_at = null;

    /**
     * @Groups({"note:read", "note:update"})
     */
    #[ORM\OneToMany(mappedBy: 'note', targetEntity: Task::class, orphanRemoval: true, cascade: ["persist"])]
    private Collection $tasks;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->created_at = (new DateTimeImmutable())->format('Y-m-d H:i');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
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

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setNote($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getNote() === $this) {
                $task->setNote(null);
            }
        }

        return $this;
    }
}

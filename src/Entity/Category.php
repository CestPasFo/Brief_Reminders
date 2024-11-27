<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $name = null;

    /**
     * @var Collection<int, Reminder>
     */
    #[ORM\OneToMany(targetEntity: Reminder::class, mappedBy: 'idCategory')]
    private Collection $idReminder;

    public function __construct()
    {
        $this->idReminder = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Reminder>
     */
    public function getIdReminder(): Collection
    {
        return $this->idReminder;
    }

    public function addIdReminder(Reminder $idReminder): static
    {
        if (!$this->idReminder->contains($idReminder)) {
            $this->idReminder->add($idReminder);
            $idReminder->setIdCategory($this);
        }

        return $this;
    }

    public function removeIdReminder(Reminder $idReminder): static
    {
        if ($this->idReminder->removeElement($idReminder)) {
            // set the owning side to null (unless already changed)
            if ($idReminder->getIdCategory() === $this) {
                $idReminder->setIdCategory(null);
            }
        }

        return $this;
    }
}

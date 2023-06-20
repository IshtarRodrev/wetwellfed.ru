<?php

namespace App\Entity;

use App\Repository\MealRepository;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\Eater;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass=MealRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Meal
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Food::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Food $food;

    /**
     * @ORM\ManyToOne(targetEntity=Eater::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Eater $eater;

    /**
     * @ORM\Column(type="float")
     */
    private float $amount;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $eatenAt;

    /**
     * @ORM\Column(type="integer", length=3)
     */
    private int $calories;

    public function __construct()
    {
        $this->eatenAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFood(): ?Food
    {
        return $this->food;
    }

    public function setFood(?Food $food): self
    {
        $this->food = $food;

        return $this;
    }

    public function getEater(): ?Eater
    {
        return $this->eater;
    }

    public function setEater(?Eater $eater): self
    {
        $this->eater = $eater;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getEatenAt(): \DateTimeInterface
    {
        return $this->eatenAt;
    }

    public function setEatenAt(\DateTimeInterface $eatenAt): self
    {
        $this->eatenAt = $eatenAt;

        return $this;
    }

    public function getDate(): ?string
    {
        return sprintf('%s)', $this->getEatenAt());
    }

    public function getCalories(): ?string
    {
        return $this->calories;
    }

    public function setCalories(string $calories): self
    {
        $this->calories = $calories;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function beforeSave(): void
    {
        $food = $this->getFood();

        $mealAmount = $this->getAmount();
        $foodCalories = $food->getCalories();
        $foodPackWeight = $food->getWeight();
        //NOTE: see App\Entity\Food\__toString

        $kal = ($foodCalories / 100) * $foodPackWeight * $mealAmount;
        $this->setCalories($kal);
    }
}


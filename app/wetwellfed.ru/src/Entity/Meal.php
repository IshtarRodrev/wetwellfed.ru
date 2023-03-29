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
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Food::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $food;

    /**
     * @ORM\ManyToOne(targetEntity=Eater::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $eater;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime")
     */
    private $eatenAt;

    /**
     * @ORM\Column(type="integer", length=3)
     */
    private $calories;

    public function __construct()
    {
        $this->eatenAt = new \DateTime();
    }

//    /**
//     * @ORM\Column(type="string", length=255)
//     */
//    private $photoFilename;
//
//    // значит, сделаем ручками. смотри внимательно
//    // я просто нашёл в книге: что сгенерировалось
//    //
//    /**
//     * @ORM\Column(type="string", length=255, options={"default": "submitted"})
//     */
//    private $state = 'submitted';
//
//    public function __toString(): string
//    {
//        return (string)$this->getEmail();
//    }
//
    public function getId(): ?int
    {
        return $this->id;
    }
//
//    public function getAuthor(): ?string
//    {
//        return $this->author;
//    }
//
//    public function setAuthor(string $author): self
//    {
//        $this->author = $author;
//
//        return $this;
//    }
//
//    public function getText(): ?string
//    {
//        return $this->text;
//    }
//
//    public function setText(string $text): self
//    {
//        $this->text = $text;
//
//        return $this;
//    }
//
//    public function getEmail(): ?string
//    {
//        return $this->email;
//    }
//
//    public function setEmail(string $email): self
//    {
//        $this->email = $email;
//
//        return $this;
//    }
//
//    public function getState(): ?string
//    {
//        return $this->state;
//    }
//
//    public function setState(string $state): self
//    {
//        $this->state = $state;
//
//        return $this;
//    }
//
//    public function getCreatedAt(): ?\DateTimeInterface
//    {
//        return $this->createdAt;
//    }
//
//    public function setCreatedAt(\DateTimeInterface $createdAt): self
//    {
//        $this->createdAt = $createdAt;
//
//        return $this;
//    }
//
//    /**
//     * @ORM\PrePersist
//     */
//    public function setCreatedAtValue()
//    {
//        $this->createdAt = new \DateTimeImmutable();
//    }
//
//    public function getPhotoFilename(): ?string
//    {
//        return $this->photoFilename;
//    }
//
//    public function setPhotoFilename(string $photoFilename): self
//    {
//        $this->photoFilename = $photoFilename;
//
//        return $this;
//    }
//
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
        //TODO: see App\Entity\Food\__toString

        $kal = ($foodCalories / 100) * $foodPackWeight * $mealAmount;
        $this->setCalories($kal);
    }
}


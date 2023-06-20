<?php

namespace App\Entity;

use App\Entity\Eater;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FoodRepository::class)
 */
class Food
{
    public const AMOUNT_TYPE_PACK = 0;
    public const AMOUNT_TYPE_GRAM = 1;
    /**
    * @ORM\Id
    * @ORM\GeneratedValue
    * @ORM\Column(type="integer")
    */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Category $category;

    /**
     * @ORM\ManyToOne(targetEntity=Eater::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Eater $eater;

    /**
    * @ORM\Column(type="string", length=255)
    */
    private string $name;

    /**
    * @ORM\Column(type="smallint", length=1)
    */
    private int $amount_type;

    /**
    * @ORM\Column(type="integer", length=3)
    */
    private int $calories;

    /**
    * @ORM\Column(type="integer")
    */
    private int $weight;

    public function __toString(): string
    {
        if ($this->amount_type === Food::AMOUNT_TYPE_PACK) {
            return sprintf("%s (%dg pack) - %dkcal ",$this->name, $this->weight, $this->calories);
        } else
            return sprintf("%s - %dkcal/100g ",$this->name, $this->calories);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAmountType(): ?int
    {
        return $this->amount_type;
    }

    public function setAmountType(int $amount_type): self
    {
        $this->amount_type = $amount_type;

        return $this;
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

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(string $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getSelectList(): ?string
    {
        $tare = $this->getAmountType()==0 ? '(' . $this->getWeight() . ' grams portion)' : '';
        return sprintf('%s %s', $this->getName(), $tare);
    }
}
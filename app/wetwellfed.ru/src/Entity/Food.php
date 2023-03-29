<?php

namespace App\Entity;

use App\Entity\Eater;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\FoodRepository; //ATTENTION!! MANUALLY ADDED!!!

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
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=Eater::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $eater;

    /**
    * @ORM\Column(type="string", length=255)
    */
    private $name;

    /**
    * @ORM\Column(type="smallint", length=1)
    */
    private $amount_type;

    /**
    * @ORM\Column(type="integer", length=3)
    */
    private $calories;

    /**
    * @ORM\Column(type="integer")
    */
    private $weight; //nullable=true

//    public function __construct()
//    {
//        $this->category = new ArrayCollection();
//    }

    public function __toString(): string // При отображении связанных сущностей (в нашем случае, конференции, прикреплённой к комментарию) EasyAdmin попытается преобразовать объект конференции в строку. Если в объекте не будет реализован "магический" метод __toString(), то по умолчанию EasyAdmin выведет имя объекта вместе с первичным ключом (например, Conference #1). Чтобы сделать название связанной сущности более понятнее, определим этот метод в классе Conference
    {
        if ($this->amount_type === Food::AMOUNT_TYPE_PACK)
        {
            return $this->name . ',  ' . $this->weight . 'g ' . "pack" . ' - ' . $this->calories . 'kcal ';
        }
        else
        return $this->name . ' - ' . $this->calories . 'kcal/100g ';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

//    public function computeSlug(SluggerInterface $slugger)
//    {
//        if (!$this->slug || '-' === $this->slug) {
//        $this->slug = (string) $slugger->slug((string) $this)->lower();
//        }
//    }

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

//    public function addComment(Comment $comment): self
//    {
//        if (!$this->comments->contains($comment)) {
//            $this->comments[] = $comment;
//            $comment->setConference($this);
//        }
//
//        return $this;
//    }
//
//    public function removeComment(Comment $comment): self
//    {
//        if ($this->comments->removeElement($comment)) {
//        // set the owning side to null (unless already changed)
//            if ($comment->getConference() === $this) {
//                $comment->setConference(null);
//            }
//        }
//
//        return $this;
//    }
//
//    public function getSlug(): ?string
//    {
//        return $this->slug;
//    }
//
//    public function setSlug(string $slug): self
//    {
//        $this->slug = $slug;
//
//        return $this;
//    }
}
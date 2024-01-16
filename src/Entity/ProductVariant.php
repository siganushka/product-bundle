<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\EnableInterface;
use Siganushka\Contracts\Doctrine\EnableTrait;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\ProductBundle\Model\VariantChoice;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

/**
 * @ORM\Entity(repositoryClass=ProductVariantRepository::class)
 * @ORM\Table(uniqueConstraints={
 *  @ORM\UniqueConstraint(columns={"product_id", "choice_value"})
 * })
 */
class ProductVariant implements ResourceInterface, EnableInterface, TimestampableInterface
{
    use EnableTrait;
    use ResourceTrait;
    use TimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="variants")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Product $product = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $choiceValue = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $price = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $inventory = null;

    /**
     * @ORM\ManyToMany(targetEntity=OptionValue::class)
     * @ORM\OrderBy({"sort": "DESC", "createdAt": "ASC", "id": "ASC"})
     *
     * @var Collection<int, OptionValue>
     */
    private Collection $choice;

    public function __construct()
    {
        $this->choice = new VariantChoice();
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getChoiceValue(): ?string
    {
        return $this->choiceValue;
    }

    public function setChoiceValue(string $choiceValue): self
    {
        $this->choiceValue = $choiceValue;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getInventory(): ?int
    {
        return $this->inventory;
    }

    public function setInventory(?int $inventory): self
    {
        $this->inventory = $inventory;

        return $this;
    }

    public function getChoice(): VariantChoice
    {
        if ($this->choice instanceof VariantChoice) {
            return $this->choice;
        }

        return new VariantChoice($this->choice->toArray());
    }

    public function setChoice(VariantChoice $choice): self
    {
        $this->choice = $choice;
        $this->choiceValue = $choice->getValue();

        return $this;
    }
}

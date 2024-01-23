<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

/**
 * @ORM\Entity(repositoryClass=ProductVariantRepository::class)
 */
class ProductVariant implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="variants")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Product $product = null;

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
        $this->choice = new ProductVariantChoice();
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

    public function getChoice(): ProductVariantChoice
    {
        if ($this->choice instanceof ProductVariantChoice) {
            return $this->choice;
        }

        return new ProductVariantChoice($this->choice->toArray());
    }

    public function setChoice(ProductVariantChoice $choice): self
    {
        $this->choice = $choice;

        return $this;
    }
}

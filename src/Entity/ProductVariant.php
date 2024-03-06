<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

/**
 * @ORM\Entity(repositoryClass=ProductVariantRepository::class)
 * @ORM\Table(uniqueConstraints={
 *  @ORM\UniqueConstraint(columns={"product_id", "code"})
 * })
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
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $code = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $price = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $inventory = null;

    /**
     * @ORM\ManyToMany(targetEntity=ProductOptionValue::class, inversedBy="variants")
     * @ORM\OrderBy({"sort": "DESC", "createdAt": "ASC", "id": "ASC"})
     *
     * @var Collection<int, ProductOptionValue>
     */
    private Collection $optionValues;

    public function __construct(array $optionValues = [])
    {
        $codes = array_map(fn (ProductOptionValue $optionValue) => $optionValue->getCode(), $optionValues);

        // [important] Generate identity from sorted value
        sort($codes);

        $this->code = \count($codes) ? implode('-', $codes) : null;
        $this->optionValues = new ArrayCollection($optionValues);
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        throw new \BadMethodCallException('The code cannot be modified anymore.');
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

    /**
     * @return Collection<int, ProductOptionValue>
     */
    public function getOptionValues(): Collection
    {
        return $this->optionValues;
    }

    public function addOptionValue(ProductOptionValue $optionValue): self
    {
        throw new \BadMethodCallException('The optionValues cannot be modified anymore.');
    }

    public function removeOptionValue(ProductOptionValue $optionValue): self
    {
        throw new \BadMethodCallException('The optionValues cannot be modified anymore.');
    }

    public function getDescriptor(): ?string
    {
        if (null === $this->product) {
            return null;
        }

        $name = $this->product->getName();
        $optionValues = $this->getOptionValues();

        if (null === $name || $optionValues->isEmpty()) {
            return $name;
        }

        return sprintf('%s【%s】', $name, $optionValues->getLabel());
    }

    /**
     * Returns whether the variant is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return null !== $this->inventory && $this->inventory <= 0;
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\MediaBundle\Entity\Media;
use Siganushka\ProductBundle\Model\CombinedOptionValues;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

/**
 * @ORM\Entity(repositoryClass=ProductVariantRepository::class)
 * @ORM\Table(uniqueConstraints={
 *  @ORM\UniqueConstraint(columns={"product_id", "option_value1_id", "option_value2_id", "option_value3_id"})
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
     * @ORM\ManyToOne(targetEntity=ProductOptionValue::class)
     */
    private ?ProductOptionValue $optionValue1 = null;

    /**
     * @ORM\ManyToOne(targetEntity=ProductOptionValue::class)
     */
    private ?ProductOptionValue $optionValue2 = null;

    /**
     * @ORM\ManyToOne(targetEntity=ProductOptionValue::class)
     */
    private ?ProductOptionValue $optionValue3 = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $price = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $inventory = null;

    /**
     * @ORM\ManyToOne(targetEntity=Media::class)
     */
    private ?Media $img = null;

    public function __construct(Product $product = null, CombinedOptionValues $optionValues = null)
    {
        $this->product = $product;

        if ($optionValues instanceof Collection) {
            [$this->optionValue1, $this->optionValue2, $this->optionValue3] = array_pad($optionValues->toArray(), 3, null);
        }
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

    public function getOptionValues(): CombinedOptionValues
    {
        return new CombinedOptionValues(array_filter([$this->optionValue1, $this->optionValue2, $this->optionValue3]));
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

    public function getImg(): ?Media
    {
        return $this->img;
    }

    public function setImg(?Media $img): self
    {
        $this->img = $img;

        return $this;
    }

    public function getDescriptor(): ?string
    {
        $productName = $this->product ? $this->product->getName() : null;
        $optionValues = $this->getOptionValues();

        if (\is_string($productName) && \is_string($optionValues->label)) {
            return sprintf('%s【%s】', $productName, $optionValues->label);
        }

        return $productName;
    }

    /**
     * Returns whether the variant is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return null !== $this->inventory && $this->inventory <= 0;
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use BenTools\CartesianProduct\CartesianProduct;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\MediaBundle\Entity\Media;
use Siganushka\ProductBundle\Model\CombinedOptionValues;
use Siganushka\ProductBundle\Repository\ProductRepository;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $name = null;

    /**
     * @ORM\ManyToOne(targetEntity=Media::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Media $img = null;

    /**
     * @ORM\OneToMany(targetEntity=ProductOption::class, mappedBy="product", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"sort": "DESC", "createdAt": "ASC", "id": "ASC"})
     *
     * @var Collection<int, ProductOption>
     */
    private Collection $options;

    /**
     * @ORM\OneToMany(targetEntity=ProductVariant::class, mappedBy="product", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt": "ASC", "id": "ASC"})
     *
     * @var Collection<int, ProductVariant>
     */
    private Collection $variants;

    public function __construct()
    {
        $this->options = new ArrayCollection();
        $this->variants = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    /**
     * @return Collection<int, ProductOption>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(ProductOption $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
            $option->setProduct($this);
        }

        return $this;
    }

    public function removeOption(ProductOption $option): self
    {
        if ($this->options->removeElement($option)) {
            if ($option->getProduct() === $this) {
                $option->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductVariant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(ProductVariant $variant): self
    {
        $codes = $this->variants->map(fn (ProductVariant $variant) => $variant->getCode());

        if (!$codes->contains($variant->getCode())) {
            $this->variants[] = $variant;
            $variant->setProduct($this);
        }

        return $this;
    }

    public function removeVariant(ProductVariant $variant): self
    {
        if ($this->variants->removeElement($variant)) {
            if ($variant->getProduct() === $this) {
                $variant->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * Returns the product is optionally.
     */
    public function isOptionally(): bool
    {
        return $this->options->count() > 0;
    }

    /**
     * @return array<int, ProductVariant>
     */
    public function getCombinedVariants(): array
    {
        if (!$this->isOptionally()) {
            return [new ProductVariant()];
        }

        $values = $this->options->map(fn (ProductOption $option) => $option->getValues());
        $cartesianProduct = new CartesianProduct($values->toArray());

        return array_map(fn (array $opitonValues) => new ProductVariant($opitonValues), $cartesianProduct->asArray());
    }

    /**
     * @return array<int, CombinedOptionValues>
     */
    public function getCombinedOptionValues(): array
    {
        $values = $this->options->map(fn (ProductOption $option) => $option->getValues());
        $cartesianProduct = new CartesianProduct($values->toArray());

        return array_map(fn (array $opitonValues) => new CombinedOptionValues($opitonValues), $cartesianProduct->asArray());
    }
}

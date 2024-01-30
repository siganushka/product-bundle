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
     * @ORM\ManyToMany(targetEntity=Option::class, inversedBy="products")
     * @ORM\OrderBy({"sort": "DESC", "createdAt": "ASC", "id": "ASC"})
     *
     * @var Collection<int, Option>
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
     * @return Collection<int, Option>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(Option $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
        }

        return $this;
    }

    public function removeOption(Option $option): self
    {
        $this->options->removeElement($option);

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
        if (!$this->variants->contains($variant)) {
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
     * @return array<int, CombinedOptionValues>
     */
    public function getCombinedOptionValues(): array
    {
        if (!$this->isOptionally()) {
            return [new CombinedOptionValues()];
        }

        $values = $this->options->map(fn (Option $option) => $option->getValues());
        $cartesianProduct = new CartesianProduct($values->toArray());

        return array_map(fn (array $opitonValues) => new CombinedOptionValues($opitonValues), $cartesianProduct->asArray());
    }
}

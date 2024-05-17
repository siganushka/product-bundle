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
use Siganushka\ProductBundle\Model\ProductVariantChoice;
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
     * @ORM\OneToMany(targetEntity=ProductOption::class, mappedBy="product", cascade={"all"}, orphanRemoval=true, fetch="EAGER")
     * @ORM\OrderBy({"createdAt": "ASC", "id": "ASC"})
     *
     * @var Collection<int, ProductOption>
     */
    private Collection $options;

    /**
     * @ORM\OneToMany(targetEntity=ProductVariant::class, mappedBy="product", cascade={"all"}, orphanRemoval=true, fetch="EAGER")
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
        $fn = fn (int $_, ProductVariant $item): bool => $item->getChoiceValue() === $variant->getChoiceValue();

        if (!$this->variants->exists($fn)) {
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
     * @return array<int, ProductVariantChoice>
     */
    public function getChoices(bool $defaultChoiceOnEmptyOptions = false): array
    {
        if ($defaultChoiceOnEmptyOptions && $this->options->isEmpty()) {
            return [new ProductVariantChoice()];
        }

        $opitonValues = [];
        foreach ($this->options as $option) {
            $values = $option->getValues();
            if (\count($values)) {
                $opitonValues[] = $values;
            }
        }

        $cartesianProduct = new CartesianProduct($opitonValues);
        $asArray = $cartesianProduct->asArray();

        return array_map(fn (array $combinedOptionValues) => new ProductVariantChoice($combinedOptionValues), $asArray);
    }
}

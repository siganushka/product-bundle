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
 * @template TMedia of Media = Media
 * @template TOption of ProductOption = ProductOption
 * @template TVariant of ProductVariant = ProductVariant
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    #[ORM\Column]
    protected ?string $name = null;

    #[ORM\Column(nullable: true)]
    protected ?string $description = null;

    /**
     * @var TMedia|null
     */
    #[ORM\ManyToOne(targetEntity: Media::class)]
    protected ?Media $img = null;

    /**
     * @var Collection<int, TOption>
     */
    #[ORM\OneToMany(targetEntity: ProductOption::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC', 'id' => 'ASC'])]
    protected Collection $options;

    /**
     * @var Collection<int, TVariant>
     */
    #[ORM\OneToMany(targetEntity: ProductVariant::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC', 'id' => 'ASC'])]
    protected Collection $variants;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
        $this->options = new ArrayCollection();
        $this->variants = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return TMedia|null
     */
    public function getImg(): ?Media
    {
        return $this->img;
    }

    /**
     * @param TMedia|null $img
     */
    public function setImg(?Media $img): static
    {
        $this->img = $img;

        return $this;
    }

    /**
     * @return Collection<int, TOption>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    /**
     * @param TOption $option
     */
    public function addOption(ProductOption $option): static
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
            $option->setProduct($this);
        }

        return $this;
    }

    /**
     * @param TOption $option
     */
    public function removeOption(ProductOption $option): static
    {
        if ($this->options->removeElement($option)) {
            if ($option->getProduct() === $this) {
                $option->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TVariant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    /**
     * @param TVariant $variant
     */
    public function addVariant(ProductVariant $variant): static
    {
        if (!$this->variants->exists(fn ($_, ProductVariant $item) => $item->getCode() === $variant->getCode())) {
            $this->variants[] = $variant;
            $variant->setProduct($this);
        }

        return $this;
    }

    /**
     * @param TVariant $variant
     */
    public function removeVariant(ProductVariant $variant): static
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
    public function generateChoices(): array
    {
        if ($this->options->isEmpty()) {
            return [new ProductVariantChoice()];
        }

        $set = [];
        foreach ($this->options as $option) {
            $values = $option->getValues();
            if ($values->count()) {
                $set[] = $values;
            }
        }

        $cartesianProduct = new CartesianProduct($set);
        $asArray = $cartesianProduct->asArray();

        return array_map(fn (array $combinedOptionValues) => new ProductVariantChoice($combinedOptionValues), $asArray);
    }
}

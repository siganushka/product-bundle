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
use Siganushka\MediaBundle\Model\MediaInterface;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductRepository;

/**
 * @template TMedia of MediaInterface = MediaInterface
 * @template TOption of AbstractProductOption = AbstractProductOption
 * @template TVariant of AbstractProductVariant = AbstractProductVariant
 */
#[ORM\MappedSuperclass(repositoryClass: ProductRepository::class)]
abstract class AbstractProduct implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    #[ORM\Column]
    protected ?string $name = null;

    #[ORM\Column(nullable: true)]
    protected ?string $summary = null;

    /**
     * @var TMedia|null
     */
    #[ORM\ManyToOne]
    protected ?MediaInterface $img = null;

    #[ORM\Column(nullable: true)]
    protected ?int $lowestPrice = null;

    #[ORM\Column(nullable: true)]
    protected ?int $highestPrice = null;

    /**
     * @var Collection<int, TOption>
     */
    #[ORM\OneToMany(targetEntity: AbstractProductOption::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected Collection $options;

    /**
     * @var Collection<int, TVariant>
     */
    #[ORM\OneToMany(targetEntity: AbstractProductVariant::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
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

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return TMedia|null
     */
    public function getImg(): ?MediaInterface
    {
        return $this->img;
    }

    /**
     * @param TMedia|null $img
     */
    public function setImg(?MediaInterface $img): static
    {
        $this->img = $img;

        return $this;
    }

    public function getLowestPrice(): ?int
    {
        return $this->lowestPrice;
    }

    public function setLowestPrice(?int $lowestPrice): static
    {
        $this->lowestPrice = $lowestPrice;

        return $this;
    }

    public function getHighestPrice(): ?int
    {
        return $this->highestPrice;
    }

    public function setHighestPrice(?int $highestPrice): static
    {
        $this->highestPrice = $highestPrice;

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
    public function addOption(AbstractProductOption $option): static
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
    public function removeOption(AbstractProductOption $option): static
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
    public function addVariant(AbstractProductVariant $variant): static
    {
        if (!$this->variants->exists(static fn ($_, AbstractProductVariant $item) => $item->getCode() === $variant->getCode())) {
            $this->variants[] = $variant;
            $variant->setProduct($this);
        }

        return $this;
    }

    /**
     * @param TVariant $variant
     */
    public function removeVariant(AbstractProductVariant $variant): static
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

        return array_map(static fn (array $combinedOptionValues) => new ProductVariantChoice($combinedOptionValues), $asArray);
    }
}

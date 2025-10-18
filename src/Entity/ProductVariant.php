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
use Siganushka\MediaBundle\Entity\Media;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

#[ORM\Entity(repositoryClass: ProductVariantRepository::class)]
#[ORM\UniqueConstraint(columns: ['product_id', 'value'])]
class ProductVariant implements ResourceInterface, EnableInterface, TimestampableInterface
{
    use EnableTrait;
    use ResourceTrait;
    use TimestampableTrait;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Product $product = null;

    #[ORM\Column(nullable: true, updatable: false)]
    protected ?string $value = null;

    #[ORM\Column(nullable: true)]
    protected ?string $label = null;

    #[ORM\Column(nullable: true)]
    protected ?int $price = null;

    #[ORM\Column(nullable: true)]
    protected ?int $stock = null;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    protected ?Media $img = null;

    /**
     * @var Collection<int, ProductOptionValue>
     */
    #[ORM\ManyToMany(targetEntity: ProductOptionValue::class, inversedBy: 'variants')]
    #[ORM\JoinTable('product_variant_value')]
    protected Collection $optionValues;

    public function __construct(?ProductVariantChoice $choice = null)
    {
        $choice ??= new ProductVariantChoice();

        $this->value = $choice->value;
        $this->label = $choice->label;
        $this->optionValues = $choice;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        throw new \BadMethodCallException('The value cannot be modified anymore.');
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        throw new \BadMethodCallException('The label cannot be modified anymore.');
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getImg(): ?Media
    {
        return $this->img;
    }

    public function setImg(?Media $img): static
    {
        $this->img = $img;

        return $this;
    }

    /**
     * @return Collection<int, ProductOptionValue>
     */
    public function getOptionValues(): Collection
    {
        return $this->optionValues;
    }

    public function addOptionValue(ProductOptionValue $optionValue): static
    {
        throw new \BadMethodCallException('The optionValues cannot be modified anymore.');
    }

    public function removeOptionValue(ProductOptionValue $optionValue): static
    {
        throw new \BadMethodCallException('The optionValues cannot be modified anymore.');
    }

    /**
     * Returns whether the variant is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return null !== $this->stock && $this->stock <= 0;
    }

    /**
     * Returns the variant name.
     */
    public function getName(): ?string
    {
        if (null === $this->product) {
            return $this->label;
        }

        $productName = $this->product->getName();
        if (\is_string($productName) && \is_string($this->label)) {
            return \sprintf('%s【%s】', $productName, $this->label);
        }

        return $productName;
    }
}

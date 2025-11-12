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
use Siganushka\Contracts\Doctrine\VersionableInterface;
use Siganushka\Contracts\Doctrine\VersionableTrait;
use Siganushka\MediaBundle\Entity\Media;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

/**
 * @template TProduct of Product = Product
 * @template TMedia of Media = Media
 * @template TOptionValue of ProductOptionValue = ProductOptionValue
 */
#[ORM\Entity(repositoryClass: ProductVariantRepository::class)]
#[ORM\UniqueConstraint(columns: ['product_id', 'code'])]
class ProductVariant implements ResourceInterface, EnableInterface, TimestampableInterface, VersionableInterface
{
    use EnableTrait;
    use ResourceTrait;
    use TimestampableTrait;
    use VersionableTrait;

    /**
     * @var TProduct|null
     */
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'variants')]
    protected ?Product $product = null;

    #[ORM\Column(nullable: true)]
    protected ?string $code = null;

    #[ORM\Column(nullable: true)]
    protected ?string $name = null;

    #[ORM\Column(nullable: true)]
    protected ?int $price = null;

    #[ORM\Column(nullable: true)]
    protected ?int $stock = null;

    /**
     * @var TMedia|null
     */
    #[ORM\ManyToOne(targetEntity: Media::class)]
    protected ?Media $img = null;

    /**
     * @var Collection<int, TOptionValue>|ProductVariantChoice<TOptionValue>
     */
    #[ORM\ManyToMany(targetEntity: ProductOptionValue::class, inversedBy: 'variants')]
    #[ORM\JoinTable('product_variant_value')]
    protected Collection $optionValues;

    /**
     * @param ProductVariantChoice<TOptionValue>|null $choice
     */
    public function __construct(?ProductVariantChoice $choice = null)
    {
        $choice ??= new ProductVariantChoice();

        $this->code = $choice->code;
        $this->name = $choice->name;
        $this->optionValues = $choice;
    }

    /**
     * @return TProduct|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @param TProduct|null $product
     */
    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        throw new \BadMethodCallException('The code cannot be modified anymore.');
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        throw new \BadMethodCallException('The name cannot be modified anymore.');
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
     * @return Collection<int, TOptionValue>|ProductVariantChoice
     */
    public function getOptionValues(): Collection
    {
        return $this->optionValues;
    }

    /**
     * @param TOptionValue $optionValue
     */
    public function addOptionValue(ProductOptionValue $optionValue): static
    {
        throw new \BadMethodCallException('The optionValues cannot be modified anymore.');
    }

    /**
     * @param TOptionValue $optionValue
     */
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
}

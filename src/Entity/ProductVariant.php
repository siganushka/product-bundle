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
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

/**
 * @template TProduct of Product = Product
 * @template TOptionValue of ProductOptionValue = ProductOptionValue
 */
#[ORM\Entity(repositoryClass: ProductVariantRepository::class)]
#[ORM\UniqueConstraint(columns: ['product_id', 'code'])]
class ProductVariant implements ResourceInterface, EnableInterface, TimestampableInterface
{
    use EnableTrait;
    use ResourceTrait;
    use TimestampableTrait;

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
     * @var Collection<int, TOptionValue>
     */
    #[ORM\ManyToMany(targetEntity: ProductOptionValue::class, inversedBy: 'variants')]
    #[ORM\JoinTable('product_variant_value')]
    protected Collection $choice;

    /**
     * @param ProductVariantChoice<TOptionValue>|null $choice
     */
    public function __construct(?ProductVariantChoice $choice = null)
    {
        $choice ??= new ProductVariantChoice();

        $this->code = $choice->code;
        $this->name = $choice->name;
        $this->choice = $choice;
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

    public function getName(): ?string
    {
        return $this->name;
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

    public function getChoice(): ProductVariantChoice
    {
        if ($this->choice instanceof ProductVariantChoice) {
            return $this->choice;
        }

        return $this->choice = new ProductVariantChoice($this->choice->toArray());
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
 * @template TProduct of AbstractProduct = AbstractProduct
 * @template TOptionValue of AbstractProductOptionValue = AbstractProductOptionValue
 */
#[ORM\MappedSuperclass(repositoryClass: ProductVariantRepository::class)]
#[ORM\UniqueConstraint(columns: ['product_id', 'code'])]
#[ORM\HasLifecycleCallbacks]
abstract class AbstractProductVariant implements ResourceInterface, EnableInterface, TimestampableInterface
{
    use EnableTrait;
    use ResourceTrait;
    use TimestampableTrait;

    /**
     * @var TProduct|null
     */
    #[ORM\ManyToOne(inversedBy: 'variants')]
    protected ?AbstractProduct $product = null;

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
    #[ORM\ManyToMany(targetEntity: AbstractProductOptionValue::class, inversedBy: 'variants')]
    #[ORM\JoinTable(name: 'product_variant_value')]
    #[ORM\JoinColumn(name: 'product_variant_id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'product_option_value_id', onDelete: 'CASCADE')]
    #[ORM\OrderBy(['option' => 'ASC', 'id' => 'ASC'])]
    protected Collection $optionValues;

    /**
     * @param ProductVariantChoice<TOptionValue> $choice
     */
    public function __construct(ProductVariantChoice $choice = new ProductVariantChoice())
    {
        $this->code = $choice->code;
        $this->name = $choice->name;
        $this->optionValues = new ArrayCollection($choice->combinedOptionValues);
    }

    /**
     * @return TProduct|null
     */
    public function getProduct(): ?AbstractProduct
    {
        return $this->product;
    }

    /**
     * @param TProduct|null $product
     */
    public function setProduct(?AbstractProduct $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    #[ORM\PreFlush]
    public function getName(): ?string
    {
        return $this->name ??= ProductVariantChoice::create(...$this->optionValues)->name;
    }

    public function resetName(): static
    {
        $this->name = null;

        return $this;
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
     * @return Collection<int, TOptionValue>
     */
    public function getOptionValues(): Collection
    {
        return $this->optionValues;
    }
}

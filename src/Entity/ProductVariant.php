<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\MediaBundle\Entity\Media;
use Siganushka\ProductBundle\Model\ProductVariantChoice;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

#[ORM\Entity(repositoryClass: ProductVariantRepository::class)]
#[ORM\UniqueConstraint(columns: ['product_id', 'choice1', 'choice2', 'choice3'])]
class ProductVariant implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Product $product = null;

    #[ORM\ManyToOne(targetEntity: ProductOptionValue::class)]
    #[ORM\JoinColumn(name: 'choice1')]
    protected ?ProductOptionValue $choice1 = null;

    #[ORM\ManyToOne(targetEntity: ProductOptionValue::class)]
    #[ORM\JoinColumn(name: 'choice2')]
    protected ?ProductOptionValue $choice2 = null;

    #[ORM\ManyToOne(targetEntity: ProductOptionValue::class)]
    #[ORM\JoinColumn(name: 'choice3')]
    protected ?ProductOptionValue $choice3 = null;

    #[ORM\Column]
    protected ?int $price = null;

    #[ORM\Column(nullable: true)]
    protected ?int $inventory = null;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    protected ?Media $img = null;

    protected ?ProductVariantChoice $choice = null;

    public function __construct(Product $product = null, ProductVariantChoice $choice = null)
    {
        $this->product = $product;
        $this->choice = $choice;

        if ($choice instanceof ProductVariantChoice) {
            [$this->choice1, $this->choice2, $this->choice3] = array_pad($choice->combinedOptionValues, 3, null);
        }
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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getInventory(): ?int
    {
        return $this->inventory;
    }

    public function setInventory(?int $inventory): static
    {
        $this->inventory = $inventory;

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
     * Returns choice for product variant.
     */
    public function getChoice(): ProductVariantChoice
    {
        if ($this->choice instanceof ProductVariantChoice) {
            return $this->choice;
        }

        return $this->choice = new ProductVariantChoice(array_filter([$this->choice1, $this->choice2, $this->choice3]));
    }

    /**
     * Returns choice value for variant.
     */
    public function getChoiceValue(): ?string
    {
        return $this->getChoice()->value;
    }

    /**
     * Returns choice label for variant.
     */
    public function getChoiceLabel(): ?string
    {
        return $this->getChoice()->label;
    }

    /**
     * Returns whether the variant is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return null !== $this->inventory && $this->inventory <= 0;
    }
}

<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\ProductBundle\Model\OptionValueCollection;
use Siganushka\ProductBundle\Repository\ProductVariantRepository;

/**
 * @ORM\Entity(repositoryClass=ProductVariantRepository::class)
 * @ORM\Table(uniqueConstraints={
 *  @ORM\UniqueConstraint(columns={"product_id", "choice"})
 * })
 */
class ProductVariant implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="variants")
     */
    private ?Product $product = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $choice = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $price = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $inventory = null;

    /**
     * @ORM\ManyToMany(targetEntity=OptionValue::class)
     * @ORM\OrderBy({"sorted": "DESC", "createdAt": "ASC", "id": "ASC"})
     *
     * @var Collection<int, OptionValue>
     */
    private Collection $optionValues;

    public function __construct()
    {
        $this->optionValues = new OptionValueCollection();
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getChoice(): ?string
    {
        return $this->choice;
    }

    public function setChoice(?string $choice): self
    {
        throw new \BadMethodCallException('The choice cannot be modified anymore.');
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getInventory(): ?int
    {
        return $this->inventory;
    }

    public function setInventory(?int $inventory): self
    {
        $this->inventory = $inventory;

        return $this;
    }

    public function getOptionValues(): OptionValueCollection
    {
        if ($this->optionValues instanceof OptionValueCollection) {
            return $this->optionValues;
        }

        return new OptionValueCollection($this->optionValues->toArray());
    }

    public function setOptionValues(OptionValueCollection $optionValues): self
    {
        $this->choice = $optionValues->getValue();
        $this->optionValues = $optionValues;

        return $this;
    }

    public function addOptionValue(OptionValue $optionValue): self
    {
        throw new \BadMethodCallException('The optionValues cannot be modified anymore.');
    }

    public function removeOptionValue(OptionValue $optionValue): self
    {
        throw new \BadMethodCallException('The optionValues cannot be modified anymore.');
    }
}

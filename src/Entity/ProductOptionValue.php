<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\MediaBundle\Entity\Media;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;

/**
 * @template TOption of ProductOption = ProductOption
 * @template TVariant of ProductVariant = ProductVariant
 * @template TMedia of Media = Media
 */
#[ORM\Entity(repositoryClass: ProductOptionValueRepository::class)]
#[ORM\UniqueConstraint(columns: ['option_id', 'code'])]
class ProductOptionValue implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    /**
     * @var TOption|null
     */
    #[ORM\ManyToOne(targetEntity: ProductOption::class, inversedBy: 'values')]
    protected ?ProductOption $option = null;

    #[ORM\Column]
    protected ?string $code = null;

    #[ORM\Column]
    protected ?string $text = null;

    /**
     * @var TMedia|null
     */
    #[ORM\ManyToOne(targetEntity: Media::class)]
    protected ?Media $img = null;

    /**
     * @var Collection<int, TVariant>
     */
    #[ORM\ManyToMany(targetEntity: ProductVariant::class, mappedBy: 'optionValues', cascade: ['all'])]
    protected Collection $variants;

    /**
     * @param TMedia|null $img
     */
    public function __construct(?string $code = null, ?string $text = null, ?Media $img = null)
    {
        $this->code = $code ?? mb_substr(md5(uniqid()), 0, 7);
        $this->text = $text;
        $this->img = $img;
        $this->variants = new ArrayCollection();
    }

    /**
     * @return TOption|null
     */
    public function getOption(): ?ProductOption
    {
        return $this->option;
    }

    /**
     * @param TOption|null $option
     */
    public function setOption(?ProductOption $option): static
    {
        $this->option = $option;

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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

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
        throw new \BadMethodCallException('The variants cannot be modified anymore.');
    }

    /**
     * @param TVariant $variant
     */
    public function removeVariant(ProductVariant $variant): static
    {
        throw new \BadMethodCallException('The variants cannot be modified anymore.');
    }
}

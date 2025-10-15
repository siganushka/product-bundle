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

#[ORM\Entity(repositoryClass: ProductOptionValueRepository::class)]
#[ORM\UniqueConstraint(columns: ['option_id', 'code'])]
class ProductOptionValue implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    #[ORM\ManyToOne(targetEntity: ProductOption::class, inversedBy: 'values')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?ProductOption $option = null;

    #[ORM\Column(updatable: false)]
    protected ?string $code = null;

    #[ORM\Column]
    protected ?string $text = null;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    protected ?Media $img = null;

    /**
     * @var Collection<int, ProductVariant>
     */
    #[ORM\ManyToMany(targetEntity: ProductVariant::class, mappedBy: 'optionValues', cascade: ['all'])]
    protected Collection $variants;

    public function __construct(?string $code = null, ?string $text = null, ?Media $img = null)
    {
        $this->code = $code ?? mb_substr(md5(uniqid()), 0, 7);
        $this->text = $text;
        $this->img = $img;
        $this->variants = new ArrayCollection();
    }

    public function getOption(): ?ProductOption
    {
        return $this->option;
    }

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
     * @return Collection<int, ProductVariant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(ProductVariant $variant): static
    {
        throw new \BadMethodCallException('The variants cannot be modified anymore.');
    }

    public function removeVariant(ProductVariant $variant): static
    {
        throw new \BadMethodCallException('The variants cannot be modified anymore.');
    }
}

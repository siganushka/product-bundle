<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

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

    /** @var Collection<int, ProductVariant> */
    #[ORM\OneToMany(targetEntity: ProductVariant::class, mappedBy: 'choice1', cascade: ['remove'])]
    protected Collection $variant1;

    /** @var Collection<int, ProductVariant> */
    #[ORM\OneToMany(targetEntity: ProductVariant::class, mappedBy: 'choice2', cascade: ['remove'])]
    protected Collection $variant2;

    /** @var Collection<int, ProductVariant> */
    #[ORM\OneToMany(targetEntity: ProductVariant::class, mappedBy: 'choice3', cascade: ['remove'])]
    protected Collection $variant3;

    #[ORM\Column]
    protected ?string $code = null;

    #[ORM\Column]
    protected ?string $text = null;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    protected ?Media $img = null;

    public function __construct(?string $code = null, ?string $text = null, ?Media $img = null)
    {
        $this->setCode($code);
        $this->setText($text);
        $this->setImg($img);
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
        $this->code = $code;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $text && $this->code ??= mb_substr(md5($text), 0, 7);
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
    public function getVariant1(): Collection
    {
        return $this->variant1;
    }

    /**
     * @return Collection<int, ProductVariant>
     */
    public function getVariant2(): Collection
    {
        return $this->variant2;
    }

    /**
     * @return Collection<int, ProductVariant>
     */
    public function getVariant3(): Collection
    {
        return $this->variant3;
    }
}

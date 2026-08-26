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
use Siganushka\MediaBundle\Model\MediaInterface;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;

/**
 * @template TOption of AbstractProductOption = AbstractProductOption
 * @template TVariant of AbstractProductVariant = AbstractProductVariant
 * @template TMedia of MediaInterface = MediaInterface
 */
#[ORM\MappedSuperclass(repositoryClass: ProductOptionValueRepository::class)]
#[ORM\UniqueConstraint(columns: ['option_id', 'code'])]
abstract class AbstractProductOptionValue implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    /**
     * @var TOption|null
     */
    #[ORM\ManyToOne(inversedBy: 'values')]
    protected ?AbstractProductOption $option = null;

    #[ORM\Column]
    protected string $code;

    #[ORM\Column]
    protected ?string $name = null;

    /**
     * @var TMedia|null
     */
    #[ORM\ManyToOne]
    protected ?MediaInterface $img = null;

    /**
     * @var Collection<int, TVariant>
     */
    #[ORM\ManyToMany(targetEntity: AbstractProductVariant::class, mappedBy: 'optionValues', cascade: ['all'])]
    protected Collection $variants;

    /**
     * @param TMedia|null $img
     */
    public function __construct(?string $code = null, ?string $name = null, ?MediaInterface $img = null)
    {
        $this->code = $code ?? substr(uniqid(), -8);
        $this->name = $name;
        $this->img = $img;
        $this->variants = new ArrayCollection();
    }

    /**
     * @return TOption|null
     */
    public function getOption(): ?AbstractProductOption
    {
        return $this->option;
    }

    /**
     * @param TOption|null $option
     */
    public function setOption(?AbstractProductOption $option): static
    {
        $this->option = $option;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
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

    /**
     * @return Collection<int, TVariant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function getVariantsCount(): int
    {
        return $this->variants->filter(static fn (AbstractProductVariant $item) => $item->isEnabled())->count();
    }
}

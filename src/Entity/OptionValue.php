<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\SortableInterface;
use Siganushka\Contracts\Doctrine\SortableTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\MediaBundle\Entity\Media;
use Siganushka\ProductBundle\Exception\ResourceDisallowRemoveException;
use Siganushka\ProductBundle\Repository\OptionValueRepository;

/**
 * @ORM\Entity(repositoryClass=OptionValueRepository::class)
 * @ORM\Table(uniqueConstraints={
 *  @ORM\UniqueConstraint(columns={"option_id", "code"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class OptionValue implements ResourceInterface, SortableInterface, TimestampableInterface, \Stringable
{
    use ResourceTrait;
    use SortableTrait;
    use TimestampableTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Option::class, inversedBy="values")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Option $option = null;

    /**
     * @ORM\Column(type="string", length=7, options={"fixed": true})
     */
    private string $code;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $text = null;

    /**
     * @ORM\ManyToOne(targetEntity=Media::class)
     */
    private ?Media $img = null;

    /**
     * @ORM\ManyToMany(targetEntity=ProductVariant::class, mappedBy="optionValues")
     *
     * @var Collection<int, ProductVariant>
     */
    private Collection $variants;

    public function __construct(string $code = null, string $text = null, Media $img = null)
    {
        if (null !== $code && !preg_match('/^[a-zA-Z0-9_]+$/', $code)) {
            throw new \InvalidArgumentException(sprintf('The code with value "%s" contains illegal character(s).', $code));
        }

        $this->code = $code ?? mb_substr(md5(uniqid()), 0, 7);
        $this->text = $text;
        $this->img = $img;
        $this->variants = new ArrayCollection();
    }

    public function getOption(): ?Option
    {
        return $this->option;
    }

    public function setOption(?Option $option): self
    {
        $this->option = $option;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        throw new \BadMethodCallException('The code cannot be modified anymore.');
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getImg(): ?Media
    {
        return $this->img;
    }

    public function setImg(?Media $img): self
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

    public function addVariant(ProductVariant $variant): self
    {
        throw new \BadMethodCallException('The variant cannot be modified anymore.');
    }

    public function removeVariant(ProductVariant $variant): self
    {
        throw new \BadMethodCallException('The variant cannot be modified anymore.');
    }

    public function __toString(): string
    {
        return (string) $this->text;
    }

    /**
     * @ORM\PreRemove
     */
    public function assertAllowedRemove(): void
    {
        if (!$this->variants->isEmpty()) {
            throw new ResourceDisallowRemoveException($this, 'variants');
        }
    }
}

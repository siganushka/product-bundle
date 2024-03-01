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
class OptionValue implements ResourceInterface, SortableInterface, TimestampableInterface
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
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $note = null;

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

    public function __construct(string $text = null, string $note = null, Media $img = null)
    {
        $this->code = mb_substr(md5(spl_object_hash($this)), 0, 7);
        $this->text = $text;
        $this->note = $note;
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

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

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

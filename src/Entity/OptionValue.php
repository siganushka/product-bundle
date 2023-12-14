<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\SortableInterface;
use Siganushka\Contracts\Doctrine\SortableTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\ProductBundle\Repository\OptionValueRepository;

/**
 * @ORM\Entity(repositoryClass=OptionValueRepository::class)
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
     * @ORM\Column(type="string")
     */
    private ?string $text = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $img = null;

    public function __construct(string $text = null, string $img = null)
    {
        $this->text = $text;
        $this->img = $img;
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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(?string $img): self
    {
        $this->img = $img;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->text;
    }
}

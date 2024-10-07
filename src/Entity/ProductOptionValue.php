<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Entity;

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

    #[ORM\Column]
    protected string $code;

    #[ORM\Column]
    protected ?string $text = null;

    #[ORM\Column(nullable: true)]
    protected ?string $note = null;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    protected ?Media $img = null;

    public function __construct(string $code = null, string $text = null, string $note = null, Media $img = null)
    {
        if (null !== $code && !preg_match('/^[a-zA-Z0-9_]+$/', $code)) {
            throw new \InvalidArgumentException(\sprintf('The code with value "%s" contains illegal character(s).', $code));
        }

        $this->code = $code ?? mb_substr(md5(uniqid()), 0, 7);
        $this->text = $text;
        $this->note = $note;
        $this->img = $img;
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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
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

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

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

    public function getDescriptor(): ?string
    {
        $optionName = $this->option ? $this->option->getName() : null;

        if (\is_string($this->text) && \is_string($optionName)) {
            return \sprintf('%s: %s', $optionName, $this->text);
        }

        return $this->text;
    }
}

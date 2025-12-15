<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Dto;

class ProductQueryDto
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?\DateTimeInterface $startAt = null,
        public readonly ?\DateTimeInterface $endAt = null,
    ) {
    }
}

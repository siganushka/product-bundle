<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Dto;

use Siganushka\GenericBundle\Dto\DateRangeDto;

class ProductQueryDto
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?DateRangeDto $created = null,
    ) {
    }
}

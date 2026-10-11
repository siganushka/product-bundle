<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Dto;

use Doctrine\Common\Collections\Expr\Comparison;
use Siganushka\GenericBundle\Attribute\QueryFilter;
use Siganushka\GenericBundle\Dto\DateRangeDto;

class ProductQueryDto
{
    public function __construct(
        #[QueryFilter(expr: Comparison::CONTAINS)]
        public readonly ?string $name = null,
        #[QueryFilter(expr: Comparison::GTE)]
        public readonly ?int $lowestPrice = null,
        #[QueryFilter(expr: Comparison::LTE)]
        public readonly ?int $highestPrice = null,
        #[QueryFilter(expr: ['startAt' => Comparison::GTE, 'endAt' => Comparison::LTE])]
        public readonly ?DateRangeDto $createdAt = null,
    ) {
    }
}

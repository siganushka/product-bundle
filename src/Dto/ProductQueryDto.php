<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Dto;

use Siganushka\GenericBundle\Dto\DateRangeDtoTrait;
use Siganushka\GenericBundle\Dto\PageQueryDtoTrait;

class ProductQueryDto
{
    use DateRangeDtoTrait;
    use PageQueryDtoTrait;

    public function __construct(public ?string $name = null)
    {
    }
}

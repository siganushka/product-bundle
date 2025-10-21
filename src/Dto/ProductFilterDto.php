<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Dto;

use Siganushka\GenericBundle\Dto\DateRangeDtoTrait;
use Siganushka\GenericBundle\Dto\PageQueryDtoTrait;

class ProductFilterDto
{
    use DateRangeDtoTrait;
    use PageQueryDtoTrait;

    public ?string $name = null;
}

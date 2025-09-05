<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Siganushka\ProductBundle\Entity\Product;

class ProductFilter extends SQLFilter
{
    public const FILTER_NAME = 'filter_name';

    public const FILTER_MAPPING = [
        self::FILTER_NAME => '{table}.name LIKE {value}',
    ];

    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        $sqlParts = [];
        if (Product::class === $targetEntity->rootEntityName || is_subclass_of($targetEntity->rootEntityName, Product::class)) {
            foreach (self::FILTER_MAPPING as $name => $sql) {
                if ($this->hasParameter($name)) {
                    $sqlParts[] = strtr($sql, ['{table}' => $targetTableAlias, '{value}' => $this->getParameter($name)]);
                }
            }
        }

        return implode(' AND ', $sqlParts);
    }
}

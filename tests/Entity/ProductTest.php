<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\ProductBundle\Entity\Product;
use Siganushka\ProductBundle\Entity\ProductOption;
use Siganushka\ProductBundle\Entity\ProductOptionValue;

class ProductTest extends TestCase
{
    public function testGenerateChoices(): void
    {
        $entity = new Product();

        $choices = $entity->generateChoices();
        static::assertCount(1, $choices);
        static::assertSame([], $choices[0]->combinedOptionValues);

        $option1 = new ProductOption('foo');
        $option1->addValue(new ProductOptionValue('f1', 'foo-1'));
        $option1->addValue(new ProductOptionValue('f2', 'foo-2'));

        $option2 = new ProductOption('bar');
        $option2->addValue(new ProductOptionValue('b1', 'bar-1'));
        $option2->addValue(new ProductOptionValue('b2', 'bar-2'));
        $option2->addValue(new ProductOptionValue('b3', 'bar-3'));

        $entity->addOption($option1);
        $entity->addOption($option2);

        $choices = $entity->generateChoices();
        static::assertCount(6, $choices);
        static::assertSame('b1-f1', $choices[0]->value);
        static::assertSame('b2-f1', $choices[1]->value);
        static::assertSame('b3-f1', $choices[2]->value);
        static::assertSame('b1-f2', $choices[3]->value);
        static::assertSame('b2-f2', $choices[4]->value);
        static::assertSame('b3-f2', $choices[5]->value);
        static::assertSame('foo-1/bar-1', $choices[0]->label);
        static::assertSame('foo-1/bar-2', $choices[1]->label);
        static::assertSame('foo-1/bar-3', $choices[2]->label);
        static::assertSame('foo-2/bar-1', $choices[3]->label);
        static::assertSame('foo-2/bar-2', $choices[4]->label);
        static::assertSame('foo-2/bar-3', $choices[5]->label);
    }

    public function testGenerateCombinedOptionValues(): void
    {
        $entity = new Product();

        $combinedOptionValues = $entity->generateCombinedOptionValues();
        static::assertCount(1, $combinedOptionValues);
        static::assertNull($combinedOptionValues[0]->code);
        static::assertNull($combinedOptionValues[0]->text);

        $option1 = new ProductOption('foo');
        $option1->addValue(new ProductOptionValue('f1', 'foo-1'));
        $option1->addValue(new ProductOptionValue('f2', 'foo-2'));

        $option2 = new ProductOption('bar');
        $option2->addValue(new ProductOptionValue('b1', 'bar-1'));
        $option2->addValue(new ProductOptionValue('b2', 'bar-2'));
        $option2->addValue(new ProductOptionValue('b3', 'bar-3'));

        $entity->addOption($option1);
        $entity->addOption($option2);

        $combinedOptionValues = $entity->generateCombinedOptionValues();
        static::assertCount(6, $combinedOptionValues);
        static::assertSame('b1-f1', $combinedOptionValues[0]->code);
        static::assertSame('b2-f1', $combinedOptionValues[1]->code);
        static::assertSame('b3-f1', $combinedOptionValues[2]->code);
        static::assertSame('b1-f2', $combinedOptionValues[3]->code);
        static::assertSame('b2-f2', $combinedOptionValues[4]->code);
        static::assertSame('b3-f2', $combinedOptionValues[5]->code);
        static::assertSame('foo-1/bar-1', $combinedOptionValues[0]->text);
        static::assertSame('foo-1/bar-2', $combinedOptionValues[1]->text);
        static::assertSame('foo-1/bar-3', $combinedOptionValues[2]->text);
        static::assertSame('foo-2/bar-1', $combinedOptionValues[3]->text);
        static::assertSame('foo-2/bar-2', $combinedOptionValues[4]->text);
        static::assertSame('foo-2/bar-3', $combinedOptionValues[5]->text);
    }
}

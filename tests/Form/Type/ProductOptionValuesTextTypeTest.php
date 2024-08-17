<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Form\Type\ProductOptionValuesTextType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ProductOptionValuesTextTypeTest extends TypeTestCase
{
    public function testAll(): void
    {
        $data = new ArrayCollection([
            new ProductOptionValue('1', 'AAA'),
            new ProductOptionValue('2', 'BBB'),
            new ProductOptionValue('3', 'CCC'),
        ]);

        $form = $this->factory->create(ProductOptionValuesTextType::class, $data, ['data_class' => null]);
        static::assertSame($data, $form->getData());

        $view = $form->createView();
        static::assertSame('AAA,BBB,CCC', $view->vars['value']);

        $form->submit('CCC,BBB,EEE');

        /** @var array<int, ProductOptionValue> */
        $data = $form->getData();

        static::assertCount(3, $data);
        static::assertInstanceOf(ProductOptionValue::class, $data[0]);
        static::assertInstanceOf(ProductOptionValue::class, $data[1]);
        static::assertInstanceOf(ProductOptionValue::class, $data[2]);
        static::assertSame('3', $data[0]->getCode());
        static::assertSame('2', $data[1]->getCode());
    }
}

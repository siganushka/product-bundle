<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Tests\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use Siganushka\ProductBundle\Entity\ProductOptionValue;
use Siganushka\ProductBundle\Form\Type\ProductOptionValuesTextType;
use Siganushka\ProductBundle\Repository\ProductOptionValueRepository;
use Symfony\Component\Form\Test\TypeTestCase;

class ProductOptionValuesTextTypeTest extends TypeTestCase
{
    public function testAll(): void
    {
        $data = [
            new ProductOptionValue('1', 'AAA'),
            new ProductOptionValue('2', 'BBB'),
            new ProductOptionValue('3', 'CCC'),
        ];

        $form = $this->factory->create(ProductOptionValuesTextType::class, $data, ['data_class' => null]);
        static::assertSame($data, $form->getData());

        $form = $this->factory->create(ProductOptionValuesTextType::class, new ArrayCollection($data), ['data_class' => null]);

        /** @var ArrayCollection<int, ProductOptionValue> */
        $formData = $form->getData();
        static::assertSame($data, $formData->toArray());

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

    public function testNullDefaultData(): void
    {
        $form = $this->factory->create(ProductOptionValuesTextType::class);
        static::assertNull($form->getData());
    }

    protected function getTypes(): array
    {
        /** @var MockObject&ProductOptionValueRepository */
        $repository = $this->createMock(ProductOptionValueRepository::class);
        $repository->expects(static::any())
            ->method('createNew')
            ->willReturnCallback(fn (...$args) => new ProductOptionValue(...$args))
        ;

        return [
            new ProductOptionValuesTextType($repository),
        ];
    }
}

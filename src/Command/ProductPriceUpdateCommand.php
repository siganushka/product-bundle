<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Siganushka\ProductBundle\Entity\AbstractProduct;
use Siganushka\ProductBundle\Repository\ProductRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('siganushka:product:price-update', 'Update the lowest and highest prices for a product based on its product variants.')]
class ProductPriceUpdateCommand extends Command
{
    private readonly ProductRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $repository = $entityManager->getRepository(AbstractProduct::class);
        \assert($repository instanceof ProductRepository);

        $this->repository = $repository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('product-id', InputArgument::OPTIONAL, 'Update specified products.')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Update all products.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('product-id');
        if (\is_string($id)) {
            $products = $this->repository->findBy(compact('id'));
            if (0 === \count($products)) {
                throw new \InvalidArgumentException(\sprintf('The specified product id "%s" was not found.', $id));
            }
        } elseif ($input->getOption('all')) {
            $products = $this->repository->findAll();
        } else {
            throw new \InvalidArgumentException('You can either specify a product id or use the --all option to update all products.');
        }

        $total = \count($products);
        $successfully = 0;

        $batchSize = 100;
        $classMetadata = $this->entityManager->getClassMetadata(AbstractProduct::class);

        foreach ($products as $product) {
            ++$successfully;

            /** @var array<string, string> */
            $identifiers = $classMetadata->getIdentifierValues($product);
            $identifier = implode('-', $identifiers);

            $message = \sprintf('[%d/%d] #%d',
                $successfully,
                $total,
                $identifier,
            );

            $output->writeln(\sprintf('%s 正在处理...', $message));

            $prices = [];
            foreach ($product->getVariants() as $variant) {
                if ($variant->isEnabled() && null !== $variant->getPrice()) {
                    $prices[] = $variant->getPrice();
                }
            }

            $product->setLowestPrice($prices ? min($prices) : null);
            $product->setHighestPrice($prices ? max($prices) : null);

            if (($successfully % $batchSize) === 0) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return Command::SUCCESS;
    }
}

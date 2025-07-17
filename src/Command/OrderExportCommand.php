<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TigerMedia\TigerConnect\Exception\OrderException;
use TigerMedia\TigerConnect\Service\OrderInterface;

#[AsCommand(
    name: 'tiger-connect:export:order',
    description: 'Manually trigger order export to ERP'
)]
class OrderExportCommand extends Command
{
    private OrderInterface $orderService;

    public function __construct(
        OrderInterface $orderService,
        ?string $name = null
    )
    {
        $this->orderService = $orderService;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument(
            'orderNumber',
            InputArgument::REQUIRED,
            'Shopware order number to be exported.'
        );
    }

    /**
     * @throws OrderException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $orderNumber = $input->getArgument('orderNumber');
        $io->title('Starting manual export...');

        try {
            $result = $this->orderService->export($orderNumber);

            if ($result === OrderInterface::FAILED) {
                $io->error('Failed to export order');
                return Command::FAILURE;
            }
        } catch (OrderException $exception) {
            $io->error($exception->getMessage());
            throw $exception;
        }

        $io->success('Order number: ' . $orderNumber . ' has been successfully exported.');
        return Command::SUCCESS;
    }
}
// src/Command/DispatchBookFetchJobCommand.php
namespace App\Command;

use App\Message\BookFetchJob;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DispatchBookFetchJobCommand extends Command
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        parent::__construct();

        // Inject the message bus into the command
        $this->bus = $bus;
    }

    protected function configure()
    {
        $this->setName('app:dispatch-book-fetch')
             ->setDescription('Dispatches a BookFetchJob message');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Dispatch the message
        $this->bus->dispatch(new BookFetchJob('Harry Potter', null));

        $output->writeln('Message dispatched!');
    }
}


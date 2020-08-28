<?php

// https://github.com/thecodeholic/php-file-watcher
// https://github.com/flint/Lurker
// https://github.com/consolidation/Robo

namespace Damian972\ReloadBundle\Command;

use App\Kernel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class FileWatcherCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'reload:watch';

    /**
     * @var int
     */
    private $serverPort;

    public function __construct(Kernel $kernel, int $serverPort)
    {
        parent::__construct($kernel);
        $this->serverPort = $serverPort;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Run Reload\'s watcher')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $process = new Process(['node', $this->bundleDir.'/../bin/watcher', $this->serverPort, $this->projectDir.'/templates']);
        $process->setTimeout(0);
        $process->run(function ($type, $buffer) use (&$io) {
            if (Process::ERR === $type) {
                $io->error($buffer);
            //echo 'ERR > '.$buffer;
            } else {
                echo $buffer;
            }
        });

        return 0;
    }
}

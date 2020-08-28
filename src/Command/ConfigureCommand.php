<?php

namespace Damian972\ReloadBundle\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfigureCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'reload:configure';

    protected function configure(): void
    {
        $this
            ->setDescription('Add required dev npm packages to your package.json')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $packageJsonPath = $this->projectDir.'/package.json';

        $content = json_decode(file_get_contents($this->bundleDir.'/../package.json'), true);
        $devDependencies = $content['devDependencies'] ?? [];
        if (file_exists($packageJsonPath)) {
            $content = json_decode(file_get_contents($packageJsonPath), true);
            $content['devDependencies'] = !empty($devDependencies) && !empty($content['devDependencies']) ?
                array_merge($content['devDependencies'], $devDependencies) : $devDependencies;
        } else {
            $content = [];
            $content['devDependencies'] = $devDependencies;
        }

        try {
            file_put_contents($packageJsonPath, json_encode(
                $content,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            ));
            $io->success('Package.json configured');
        } catch (Exception $e) {
            $io->error($e->getMessage());
        }

        return 0;
    }
}

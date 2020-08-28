<?php

namespace Damian972\ReloadBundle\Command;

use App\Kernel;
use Symfony\Component\Console\Command\Command;

class AbstractCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName;

    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var string
     */
    protected $bundleDir;

    public function __construct(Kernel $kernel)
    {
        parent::__construct(self::$defaultName);
        $this->projectDir = $kernel->getProjectDir();
        $this->bundleDir = rtrim($kernel->locateResource('@ReloadBundle'), '/');
    }
}

<?php

namespace House\Command;

use House\House;
use House\Model\Boiler;
use House\Model\GasBoiler;
use House\Helper\GPIOHelper;
use House\Helper\SidHelper;
use House\Model\Gpio;
use House\Service\BoilerService;
use House\Service\SessionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Class HouseCommand
 * @package House\Command
 */
class HouseCommand extends LoopCommand
{
    /**
     * @var BoilerService
     */
    protected $boilerService;

    /**
     * @var Gpio
     */
    protected $boilerRelay;

    /**
     * @var Boiler
     */
    protected $boiler;

    protected function configure()
    {
        $this
            ->setName('boiler')
            ->setDescription('Starts the boiler litener ')
            ->setHelp(<<<EOT
Boiler is actively monitoring the temperature changes and takes action in case of (configurable) threshold
<info>php bin/console boiler</info>
EOT
            )
        ;
    }

    /**
     * Get needed services
     */
    public function setup()
    {
        $this->boilerService = $this->getHouse()->boilerService();
        $this->boiler = new Boiler($this->getHouse()->config()->get('sensors')[$this->getHouse()->config()->get('mainSensor')]);
        $this->boilerRelay = new Gpio($this->getHouse()->config()->get('relay_pin'));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function tick(InputInterface $input, OutputInterface $output)
    {
        try {

            $this->boilerService->monitorBoiler($this->boiler, $this->boilerRelay);

        } catch (\Exception $e) {
            echo $e->getMessage();
            $this->getHouse()->logger()->critical($e->getMessage());
        }
    }
}

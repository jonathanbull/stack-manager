<?php

/*
 * This file is part of the Stack Manager package.
 *
 * (c) Royal Opera House Covent Garden Foundation <website@roh.org.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ROH\Bundle\StackManagerBundle\Command;

use ROH\Bundle\StackManagerBundle\Mapper\StackApiMapper;
use ROH\Bundle\StackManagerBundle\Mapper\StackConfigMapper;
use ROH\Bundle\StackManagerBundle\Service\StackComparisonService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to compare the template and parameters of a stack in the
 * CloudFormation API with what would be generated by the configuration now
 *
 * @author Robert Leverington <robert.leverington@roh.org.uk>
 */
class CompareStackCommand extends Command
{
    protected $apiStackMapper;

    protected $configStackMapper;

    protected $stackComparisonService;

    public function __construct(
        StackApiMapper $apiStackMapper,
        StackConfigMapper $configStackMapper,
        StackComparisonService $stackComparisonService
    ) {
        $this->apiStackMapper = $apiStackMapper;
        $this->configStackMapper = $configStackMapper;
        $this->stackComparisonService = $stackComparisonService;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('stack-manager:compare-stack')
            ->setDescription('Compare the template and parameters of a given stack in AWS with what would be generated if that stack was created now')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the stack to compare'
            )
            ->addOption(
                'scaling-profile',
                null,
                InputOption::VALUE_OPTIONAL,
                'Scaling profile to use for the stack',
                'default'
            )
            ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $scalingProfile = $input->getOption('scaling-profile');

        $stackA = $this->apiStackMapper->create($name);
        $stackB = $this->configStackMapper->create(
            $stackA->getTemplate()->getName(),
            $stackA->getEnvironment(),
            $scalingProfile,
            $name
        );

        $output->writeLn('<info>Changes to the CloudFormation parameters:</info>');
        $output->write($this->coloriseUnifiedDiff($this->stackComparisonService->compareParameters(
            $stackA->getParameters(),
            $stackB->getParameters()
        )));

        $output->writeLn('<info>Changes to the CloudFormation template:</info>');
        $output->write($this->coloriseUnifiedDiff($this->stackComparisonService->compareTemplate(
            $stackA->getTemplate(),
            $stackB->getTemplate()
        )));
    }

    /**
     * Add Symfony console color tags to a unified diff.  Added lines become
     * green, deleted lines become red.
     *
     * @param string $diff Unified diff to colorise.
     * @return string Colorised unified diff.
     */
    protected function coloriseUnifiedDiff($diff)
    {
        $diff = preg_replace('#^\+.*$#m', '<fg=green>$0</fg=green>', $diff);
        $diff = preg_replace('#^\-.*$#m', '<fg=red>$0</fg=red>', $diff);

        return $diff;
    }
}

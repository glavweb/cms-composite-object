<?php

/*
 * This file is part of the GLAVWEB.cms CmsCompositeObject package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CmsCompositeObject\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Glavweb\CmsCompositeObject\Helper\LoadFixtureHelper;
use Glavweb\CmsRestClient\CmsRestClient;

/**
 * Class FixtureCommand
 *
 * @package Glavweb\CmsCompositeObject
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class FixtureCommand extends Command
{
    /**
     * @var \Application
     */
    private $silexApplication;

    /**
     * @var LoadFixtureHelper
     */
    private $loadFixtureHelper;

    /**
     * FixtureCommand constructor.
     *
     * @param \Application      $application
     * @param LoadFixtureHelper $loadFixtureHelper
     */
    public function __construct(\Application $application, LoadFixtureHelper $loadFixtureHelper)
    {
        parent::__construct(null);
        
        $this->silexApplication  = $application;
        $this->loadFixtureHelper = $loadFixtureHelper;
    }

    /**
     * Configuring the Command
     */
    protected function configure(): void
    {
        $this
            ->setName('fixture')
            ->setDescription('Creates fixtures.')
            ->setHelp("This command crete create fixtures.")
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var CmsRestClient $cmsRestClient */
        /** @var Session $session */
        $app = $this->silexApplication;
        $cmsRestClient = $app['cms.rest_client'];
        
        $fixtures = include $app['fixture.objects_file'];
        $fixtureClassesOnly = $this->loadFixtureHelper->getFixtureClassesOnly($fixtures);

        // Loda fixture structure
        $response = $cmsRestClient->post('fixtures', [
            'form_params' => [
                'data' => json_encode($fixtureClassesOnly),
            ]
        ], true);

        if ($response->getStatusCode() == 204) {
            // Load fixture instances
            foreach ($fixtures as $fixtureItem) {
                if (!isset($fixtureItem['instances'])) {
                    continue;
                }

                $instancesData = $fixtureItem['instances'];
                $classData     = $fixtureItem['class'];
                $className     = $classData['name'];

                foreach ($instancesData as $fixtureId => $instanceData) {
                    $instanceData = $this->loadFixtureHelper->prepareFixtureInstanceForLoad($instanceData, $classData);

                    $response = $cmsRestClient->post('fixtures/instance/' . $className, [
                        'form_params' => [
                            'fixtureId' => $fixtureId,
                            'data'  => json_encode($instanceData),
                        ]
                    ], true);

                    if ($response->getStatusCode() != 204) {
                        $output->writeln('Upload fixtures has error!.' . $response->getBody());

                        return 1;
                    }
                }
            }

            $output->writeln('Upload fixtures is completed successfully.');

            return null;
        }

        $output->writeln('Upload fixtures has error!.' . $response->getBody());

        return 1;
    }
}

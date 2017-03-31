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
    protected function configure()
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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var CmsRestClient $cmsRestClient */
        /** @var Session $session */
        $app = $this->silexApplication;
        $cmsRestClient = $app['cms.rest_client'];
        
        $fixtures = include $app['fixture.objects_file'];
        $fixtures = $this->loadFixtureHelper->prepareFixturesForLoad($fixtures);
        
        $response = $cmsRestClient->post('fixtures', [
            'form_params' => [
                'data' => json_encode($fixtures),
            ]
        ], true);

        if ($response->getStatusCode() == 204) {
            $output->writeln('Upload fixtures is completed successfully.');

            return null;
        }

        $output->writeln('Upload fixtures has error!.' . $response->getBody());

        return 1;
    }
}

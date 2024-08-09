<?php

namespace App\Tests\System;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class SystemTestCase extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param array<string, mixed> $body
     * @return string
     */
    protected function getRequestBody(array $body): string
    {
        $jsonBody = json_encode($body);

        if (!$jsonBody) {
            $jsonBody = '';
        }

        return $jsonBody;
    }

    /**
     * @param KernelBrowser $client
     * @return array<string, mixed>
     */
    protected function getResponseContent(KernelBrowser $client): array
    {
        $content = $client->getResponse()->getContent();
        return $content ? json_decode($content, true) : [];
    }

    protected function getEntityManager(ContainerInterface $container): EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        return $em;
    }

    protected function getDatabaseTool(ContainerInterface $container): AbstractDatabaseTool
    {
        /** @var DatabaseToolCollection $databaseToolCollection */
        $databaseToolCollection = $container->get(DatabaseToolCollection::class);
        return $databaseToolCollection->get();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->databaseTool);
    }
}

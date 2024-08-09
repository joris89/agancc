<?php

namespace App\Tests\Integration;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class IntegrationTestCase extends KernelTestCase
{
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->updateSchema($kernel);
        $this->truncateDB();
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        return $entityManager;
    }

    private function truncateDB(): void
    {
        $purger = new ORMPurger($this->getEntityManager());
        $purger->purge();
    }

    private function updateSchema(KernelInterface $kernel): void
    {
        $application = new Application($kernel);

        $command = $application->find('doctrine:schema:update');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => true, '--env' => 'test']);
    }
}

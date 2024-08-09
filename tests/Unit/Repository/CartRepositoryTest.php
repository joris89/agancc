<?php

namespace App\Tests\Unit\Repository;

use App\Entity\Cart;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartRepositoryTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private CartRepository&MockObject $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->getMockBuilder(CartRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntityManager', 'find'])
            ->getMock();
    }

    public function testConstruct(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new CartRepository($registry);

        $this->assertInstanceOf(CartRepository::class, $repository);
    }

    public function testSave(): void
    {
        $cart = new Cart(); // Ersetze dies durch das tatsächliche CartItem-Objekt

        $this->repository
            ->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->entityManager);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($cart);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->repository->save($cart);

        $this->assertSame($cart, $result);
    }
}

<?php

namespace App\Tests\Unit\Repository;

use App\Entity\CartItem;
use App\Repository\CartItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartItemRepositoryTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;

    private CartItemRepository&MockObject $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->getMockBuilder(CartItemRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntityManager', 'find'])
            ->getMock();
    }

    public function testConstruct(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new CartItemRepository($registry);

        $this->assertInstanceOf(CartItemRepository::class, $repository);
    }

    public function testSave(): void
    {
        $cartItem = new CartItem(); // Ersetze dies durch das tatsächliche CartItem-Objekt

        $this->repository
            ->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->entityManager);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($cartItem);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->repository->save($cartItem);

        $this->assertSame($cartItem, $result);
    }

    public function testRemoveCartItem(): void
    {
        $cartItem = new CartItem();

        $this->repository
            ->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($cartItem);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->repository->removeCartItem($cartItem);
    }
}

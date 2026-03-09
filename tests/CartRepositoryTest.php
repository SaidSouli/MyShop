<?php

namespace App\Tests\Repository;

use App\Entity\Cart;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CartRepositoryTest extends KernelTestCase
{
    private EntityManager $entityManager;
    private CartRepository $cartRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->cartRepository = $this->entityManager
            ->getRepository(Cart::class);
    }

    public function testFindOneByUserReturnsCart(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setPassword('password');

        $cart = new Cart();
        $cart->setUser($user);
        $cart->setCreatedAt(new \DateTimeImmutable());
        $cart->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($user);
        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        $foundCart = $this->cartRepository->findOneByUser($user);

        $this->assertInstanceOf(Cart::class, $foundCart);
        $this->assertSame($user->getId(), $foundCart->getUser()->getId());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}

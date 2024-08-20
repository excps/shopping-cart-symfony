<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Cart;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CartFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $cart1 = new Cart();
        $cart1->setCode('TestCartONE');
        $cart1->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        $manager->persist($cart1);
        $manager->flush();
    }
}

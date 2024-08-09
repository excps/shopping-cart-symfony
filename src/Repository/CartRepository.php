<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\CartItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Cart>
 */
class CartRepository extends ServiceEntityRepository
{
    /**
     * CartRepository constructor.
     *
     * @param ManagerRegistry $registry the manager registry for Doctrine
     * @param LoggerInterface $logger   the logger interface for logging information
     */
    public function __construct(ManagerRegistry $registry, private LoggerInterface $logger)
    {
        parent::__construct($registry, Cart::class);
    }

    /**
     * Retrieves all cart entities from the database.
     *
     * @return mixed[]
     */
    public function myFindAll(): mixed
    {
        $this->logger->info('Getting all carts in: ' . __METHOD__);

        $query = $this->createQueryBuilder('c')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Creates a new Cart entity.
     *
     * This function initializes a new Cart object, sets a unique code using UUID,
     * sets the current date and time as the creation date, and retrieves the total price.
     *
     * @return Cart the newly created Cart object
     */
    public function createCart(): Cart
    {
        $this->logger->info('Creating new cart');

        $cart = new Cart();
        $cart->setCode(Uuid::v4()->toString());
        $cart->setCreatedAt(new \DateTimeImmutable('now'));
        $cart->getTotalPrice();

        $this->getEntityManager()->persist($cart);
        $this->getEntityManager()->flush();

        return $cart;
    }

    /**
     * Removes a Cart entity from the database.
     *
     * This function logs the removal action, removes the specified Cart entity from the database,
     * and flushes the changes to ensure the removal is persisted.
     *
     * @param Cart $cart the Cart entity to be removed
     */
    public function removeCart(Cart $cart): void
    {
        $this->logger->error('Removing cart: ' . $cart->getId());

        $this->getEntityManager()->remove($cart);
        $this->getEntityManager()->flush();
    }

    /**
     * Finds a Cart entity by its ID.
     *
     * This function queries the database to find a Cart entity that matches the given ID.
     * It also retrieves the associated CartItem entities to populate the Cart object.
     */
    public function findById(int $id): mixed
    {
        $this->logger->info('Getting cart by ID: ' . $id);

        $cart = $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->leftJoin('c.cartItems', 'ci')
            ->getQuery()
            ->getOneOrNullResult();

        if (!$cart instanceof Cart) {
            return null;
        }

        return $cart;
    }

    /**
     * Adds an item to the specified cart.
     *
     * This function persists the given CartItem entity, adds it to the Cart entity,
     * recalculates the total price of the cart, and then persists and flushes the updated Cart entity.
     *
     * @param Cart     $cart the Cart entity to which the item will be added
     * @param CartItem $item the CartItem entity to be added to the cart
     *
     * @return Cart the updated Cart entity with the new item added
     */
    public function addItem(Cart $cart, CartItem $item): Cart
    {
        $this->logger->info('Adding item to cart: ' . $item->getId());

        $this->getEntityManager()->persist($item);
        $cart->addCartItem($item);
        $cart->getTotalPrice();
        $this->getEntityManager()->persist($cart);
        $this->getEntityManager()->flush();

        return $cart;
    }

    /**
     * Deletes an item from the specified cart.
     *
     * This function logs the deletion action, removes the given CartItem entity from the Cart entity,
     * recalculates the total price of the cart, and then persists and flushes the updated Cart entity.
     *
     * @param Cart     $cart the Cart entity from which the item will be removed
     * @param CartItem $item the CartItem entity to be removed from the cart
     *
     * @return Cart the updated Cart entity with the item removed
     */
    public function deleteItem(Cart $cart, CartItem $item): Cart
    {
        $this->logger->info('Adding item to cart: ' . $item->getId());
        $this->getEntityManager()->persist($item);
        $cart->removeCartItem($item);
        $cart->getTotalPrice();
        $this->getEntityManager()->persist($cart);
        $this->getEntityManager()->flush();

        return $cart;
    }

    /**
     * Saves the given Cart entity to the database.
     *
     * This function logs the save action, recalculates the total price of the cart,
     * persists the Cart entity, and flushes the changes to ensure they are saved in the database.
     *
     * @param Cart $cart the Cart entity to be saved
     */
    public function save(Cart $cart): Cart
    {
        $this->logger->info('Saving Cart: ' . $cart->getId());
        $cart->gettotalPrice();
        $this->getEntityManager()->persist($cart);
        $this->getEntityManager()->flush();

        return $cart;
    }
}

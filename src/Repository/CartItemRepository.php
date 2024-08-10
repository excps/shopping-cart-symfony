<?php

namespace App\Repository;

use App\Entity\CartItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<CartItem>
 */
class CartItemRepository extends ServiceEntityRepository
{
    /**
     * Constructor for the CartItemRepository.
     *
     * This function initializes the repository with the given ManagerRegistry and LoggerInterface.
     *
     * @param ManagerRegistry $registry the ManagerRegistry instance for managing the entity
     * @param LoggerInterface $logger   the LoggerInterface instance for logging actions
     */
    public function __construct(ManagerRegistry $registry, private LoggerInterface $logger)
    {
        parent::__construct($registry, CartItem::class);
    }

    /**
     * Finds a CartItem entity by its ID and the associated cart ID.
     *
     * This function queries the database to find a CartItem entity that matches the given item ID and cart ID.
     * It logs the action of retrieving the cart item.
     *
     * @param int $item_id the ID of the cart item to be retrieved
     * @param int $cart_id the ID of the cart to which the item belongs
     *
     * @return CartItem|null the CartItem entity if found, or null if no matching entity is found
     */
    public function findCartItem(int $item_id, int $cart_id): ?CartItem
    {
        $this->logger->info('Getting cart item by id: ' . $item_id);

        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->andWhere('c.cart = :cid')
            ->setParameter('id', $item_id)
            ->setParameter('cid', $cart_id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Saves a CartItem entity to the database.
     *
     * This function persists the given CartItem entity to the database and flushes the changes.
     * It also logs the action of updating the item.
     *
     * @param CartItem $item the CartItem entity to be saved
     */
    public function save(CartItem $item): void
    {
        $this->logger->info('Updating Item: ' . $item->getId());
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }

    /**
     * Deletes a CartItem entity from the database.
     *
     * This function removes the given CartItem entity from the database and flushes the changes.
     * It also logs the action of deleting the item.
     *
     * @param CartItem $item the CartItem entity to be deleted
     */
    public function delete(CartItem $item): void
    {
        $this->logger->info('Deleting Item: ' . $item->getId());
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }
}

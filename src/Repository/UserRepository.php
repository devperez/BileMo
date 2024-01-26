<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findAllWithPaginationByCustomer($customer, $page, $limit)
    {
        // Fetch the index of the first result
        $firstResult = ($page - 1) * $limit;
        
        $qu = $this->createQueryBuilder('u')
            ->where('u.customer = :customer')
            ->setParameter('customer', $customer)
            ->setFirstResult($firstResult)
            ->setMaxResults($limit);
        
        return $qu->getQuery()->getResult();
    }

    public function countAllByCustomer($customerId)
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.customer = :customerId')
            ->setParameter('customerId', $customerId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

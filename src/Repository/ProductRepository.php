<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }
    public function findFiltered(?Category $category=null, ?string $search=null ) : QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.createdAt', 'DESC');
        
        if ($category) {
            $qb->andWhere('p.category = :category')
               ->setParameter('category', $category);
        }
        
        if ($search) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('p.name', ':search'),
                $qb->expr()->like('p.description', ':search')
            ))
            ->setParameter('search', '%' . $search . '%');
        }
        
        return $qb;
    }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findByIds (array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $products = $this->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->andWhere('p.isActive = :active')
            ->setParameter('ids', $ids)
            ->setParameter('active', true)
            ->getQuery()
            ->getResult()
        ;
        $indexed = [];
        foreach ($products as $product) {
            $indexed[$product->getId()] = $product;
        }
        return $indexed;
    }
    public function findAllActiveProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }      
     public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult()
        ;
    }
    public function findBySearch(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name LIKE :query OR p.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult()
        ;

    }
    public function findActiveById(int $id): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->andWhere('p.isActive = :active')
            ->setParameter('id', $id)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }   
    public function findActiveBySlug(string $slug): ?Product
{
    return $this->createQueryBuilder('p')
        ->leftJoin('p.category', 'c')
        ->addSelect('c')
        ->where('p.slug = :slug')
        ->andWhere('p.isActive = :active')
        ->setParameter('slug', $slug)
        ->setParameter('active', true)
        ->getQuery()
        ->getOneOrNullResult();
}
}

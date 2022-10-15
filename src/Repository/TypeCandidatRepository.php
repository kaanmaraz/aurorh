<?php

namespace App\Repository;

use App\Entity\TypeCandidat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeCandidat>
 *
 * @method TypeCandidat|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeCandidat|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeCandidat[]    findAll()
 * @method TypeCandidat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeCandidatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeCandidat::class);
    }

    public function add(TypeCandidat $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TypeCandidat $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findMaxId(){
        return $this->createQueryBuilder('t')
           ->select("max(t.id)")
           ->getQuery()
           ->getSingleScalarResult()
       ;
    }

    public function findMinId(){
        return $this->createQueryBuilder('t')
           ->select("min(t.id)")
           ->getQuery()
           ->getSingleScalarResult()
       ;
    }

    public function findIdSuivant(int $id){
        return $this->createQueryBuilder('t')
            ->select("t.id")
            ->andWhere("t.id > :valid")
            ->setParameter("valid", $id)
            ->orderBy("t.id", "ASC")
            ->setMaxResults(1)
           ->getQuery()
           ->getSingleScalarResult()
       ;
    }

//    /**
//     * @return TypeCandidat[] Returns an array of TypeCandidat objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TypeCandidat
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

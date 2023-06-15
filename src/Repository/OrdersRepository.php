<?php

namespace App\Repository;

use App\Entity\Orders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Players;
use App\Entity\Teams;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Orders>
 *
 * @method Orders|null find($id, $lockMode = null, $lockVersion = null)
 * @method Orders|null findOneBy(array $criteria, array $orderBy = null)
 * @method Orders[]    findAll()
 * @method Orders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orders::class);
    }

    public function save(Orders $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Orders $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   /**
    * @return Orders[] Returns an array of Orders objects
    */
   public function findByInterestTeam($value): array
   {
       return $this->createQueryBuilder('o')
            ->select(['o'])
            ->addselect(['t.name','p.firstName','p.lastName','p.market_value'])
            ->leftJoin(
            Teams::class,'t',Join::WITH, 'o.team_id=t.id'
            )
            ->leftJoin(
                Players::class,'p',Join::WITH, 'o.player_id=p.id'
                )
           ->andWhere('o.interested_team = :val')
           ->andWhere('o.status = :status')
           ->setParameter('val', $value)
           ->setParameter('status', 1)
           ->orderBy('o.id', 'ASC')
           ->setMaxResults(10)
           ->getQuery()
           ->getResult()
       ;
   }

    /**
    * @return Orders[] Returns an array of Orders objects
    */
    public function findByTeam($value): array
    {
        return $this->createQueryBuilder('o')
             ->select(['o'])
             ->addselect(['t.name','p.firstName','p.lastName','p.market_value'])
             ->leftJoin(
             Teams::class,'t',Join::WITH, 'o.interested_team=t.id'
             )
             ->leftJoin(
                Players::class,'p',Join::WITH, 'o.player_id=p.id'
                )
            ->andWhere('o.team_id = :val')
            ->andWhere('o.status = :status')
           ->setParameter('val', $value)
           ->setParameter('status', 1)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

     /**
    * @return Orders[] Returns an array of Orders objects
    */
    public function UpdateSales($id, $newTeam): void
    {
        // dd($id);
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'Update App\Entity\Orders p set p.status=:status
            WHERE p.player_id =:id'
        )->setParameter('id', $id)
        ->setParameter('status', 0);
        $query->execute();
        $entityManager->createQuery(
            'Update App\Entity\Players p set p.team_id=:team
            WHERE p.id =:id'
        )->setParameter('id', $id)
        ->setParameter('team', $newTeam)->execute();
        return;
    }

//    public function findOneBySomeField($value): ?Orders
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

<?php

namespace App\Repository;

use App\Entity\Players;
use App\Entity\Teams;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @extends ServiceEntityRepository<Players>
 *
 * @method Players|null find($id, $lockMode = null, $lockVersion = null)
 * @method Players|null findOneBy(array $criteria, array $orderBy = null)
 * @method Players[]    findAll()
 * @method Players[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Players::class);
    }

    public function save(Players $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Players $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Players[] Returns an array of Players objects
//     */
   public function findByTeam($value): array
   {
       return $this->createQueryBuilder('p')
            ->select(['p'])
            ->addselect(['t.name'])
           ->andWhere('p.team_id = :val')
           ->leftJoin(
            Teams::class,'t',Join::WITH, 'p.team_id=t.id'
           )
           ->setParameter('val', $value)
           ->orderBy('p.id', 'ASC')
        //    ->setMaxResults(10)
           ->getQuery()
           ->getResult()
       ;
   }

   public function findAllTeam(): array
   {
       return $this->createQueryBuilder('p')
            ->select(['p'])
            ->addselect(['t.name'])
           ->leftJoin(
            Teams::class,'t',Join::WITH, 'p.team_id=t.id'
           )
           ->orderBy('p.team_id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
   }
   

//    public function findOneBySomeField($value): ?Players
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

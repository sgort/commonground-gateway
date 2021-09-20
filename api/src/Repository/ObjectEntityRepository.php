<?php

namespace App\Repository;

use App\Entity\ObjectEntity;
use App\Entity\Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ObjectEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method ObjectEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method ObjectEntity[]    findAll()
 * @method ObjectEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ObjectEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ObjectEntity::class);
    }

   /**
    * @return ObjectEntity[] Returns an array of ObjectEntity objects
    */

   // typecast deze shizle
    public function findByEntity($entity, $filters = [],  $offset = 0, $limit = 25 )
    {
        $query = $this->createQueryBuilder('o')
            ->andWhere('o.entity = :entity')
            ->setParameter('entity', $entity);

        if(!empty($filters)){
            $filterCheck = $this->getFilterParameters($entity);
            $query ->leftJoin('o.objectValues', 'value');
            $level = 0;

            foreach($filters as $key=>$value){
                if(!in_array($key,$filterCheck)){
                    unset($filters[$key]);
                    continue;
                }

                // let not dive to deep
                if (!strpos($key, '.')) {
                $query->andWhere('value.stringValue = :'.$key)
                      ->setParameter($key, $value);
                }
                else{
                    if($level == 0){
                        $query ->leftJoin('value.objectEntity', 'subObject');
                        $query ->leftJoin('subObject.objectValues', 'subValue');
                    }
                    $query->andWhere('subValue.stringValue = :'.$key)
                        ->setParameter($key, $value);
                }

                //var_dump($key.':'.$value);

                // lets suport level 1
            }
        }


        return $query
            // filters toevoegen
            ->setFirstResult( $offset )
            ->setMaxResults( $limit )
            ->getQuery()
            ->getResult()
        ;
    }


    private function getAllValues(string $atribute, string $value): array
    {

    }

    private function getFilterParameters(Entity $Entity, string $prefix = '', int $level = 1): array
    {
        $filters = [];

        foreach($Entity->getAttributes() as $attribute){
            if($attribute->getType() == 'string' && $attribute->getSearchable()){
                $filters[]= $prefix.$attribute->getName();
            }
            elseif($attribute->getObject()  && $level < 5){
                $filters = array_merge($filters, $this->getFilterParameters($attribute->getObject(), $attribute->getName().'.',  $level+1));
            }
            continue;
        }

        return $filters;
    }

    // Filter functie schrijven, checken op betaande atributen, zelf looping
    // voorbeeld filter student.generaldDesription.landoforigen=NL
    //                  entity.atribute.propert['name'=landoforigen]
    //                  (objectEntity.value.objectEntity.value.name=landoforigen and
    //                  objectEntity.value.objectEntity.value.value=nl)


    /*
    public function findOneBySomeField($value): ?ObjectEntity
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
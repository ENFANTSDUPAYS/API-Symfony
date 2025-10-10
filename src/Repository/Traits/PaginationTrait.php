<?php

namespace App\Repository\Traits;

trait PaginationTrait
{
    public function findAllWithPagination(int $page, int $limit){
        $qb = $this->createQueryBuilder("b")
        ->setFirstResult(($page -1) * $limit)
        ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }
}
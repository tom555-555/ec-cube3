<?php

namespace Eccube\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CategoryRepository extends EntityRepository
{
    /**
     * @param  \Eccube\Entity\Category|null $Parent
     * @return \Eccube\Entity\Category[]
     */
    public function getList($Parent = null)
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.rank', 'DESC');
        if ($Parent) {
            $qb->where('c.Parent = :Parent')->setParameter('Parent', $Parent);
        } else {
            $qb->where('c.Parent IS NULL');
        }
        $Categories = $qb->getQuery()
            ->getResult();

        return $Categories;
    }

    /**
     * @param  \Eccube\Entity\Category $Category
     * @return void
     */
    public function up(\Eccube\Entity\Category $Category)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            $rank = $Category->getRank();
            $Parent = $Category->getParent();

            if ($Parent) {
                $CategoryUp = $this->createQueryBuilder('c')
                    ->where('c.rank > :rank AND c.Parent = :Parent')
                    ->setParameter('rank', $rank)
                    ->setParameter('Parent', $Parent)
                    ->orderBy('c.rank', 'ASC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getSingleResult();
            } else {
                $CategoryUp = $this->createQueryBuilder('c')
                    ->where('c.rank > :rank AND c.Parent IS NULL')
                    ->setParameter('rank', $rank)
                    ->orderBy('c.rank', 'ASC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getSingleResult();
            }

            $this_count = $Category->countBranches();
            $up_count = $CategoryUp->countBranches();

            $Category->calcChildrenRank($em, $up_count);
            $CategoryUp->calcChildrenRank($em, $this_count * -1);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return true;
    }

    /**
     * @param  \Eccube\Entity\Category $Category
     * @return bool
     */
    public function down(\Eccube\Entity\Category $Category)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            $rank = $Category->getRank();
            $Parent = $Category->getParent();

            if ($Parent) {
                $CategoryDown = $this->createQueryBuilder('c')
                    ->where('c.rank < :rank AND c.Parent = :Parent')
                    ->setParameter('rank', $rank)
                    ->setParameter('Parent', $Parent)
                    ->orderBy('c.rank', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getSingleResult();
            } else {
                $CategoryDown = $this->createQueryBuilder('c')
                    ->where('c.rank < :rank AND c.Parent IS NULL')
                    ->setParameter('rank', $rank)
                    ->orderBy('c.rank', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getSingleResult();
            }

            $this_count = $Category->countBranches();
            $down_count = $CategoryDown->countBranches();

            $Category->calcChildrenRank($em, $down_count * -1);
            $CategoryDown->calcChildrenRank($em, $this_count);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return true;
    }

    /**
     * @param  \Eccube\Entity\Category $Category
     * @return bool
     */
    public function save(\Eccube\Entity\Category $Category)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            if (!$Category->getId()) {
                $Parent = $Category->getParent();
                if ($Parent) {
                    $rank = $Parent->getRank() - 1;
                } else {
                    $rank = $this->createQueryBuilder('c')
                        ->select('MAX(c.rank)')
                        ->getQuery()
                        ->getSingleScalarResult();
                }
                if (!$rank) {
                    $rank = 0;
                }
                $Category->setRank($rank + 1);
                $Category->setDelFlg(0);

                $em->createQueryBuilder()
                    ->update('Eccube\Entity\Category', 'c')
                    ->set('c.rank', 'c.rank + 1')
                    ->where('c.rank > :rank')
                    ->setParameter('rank', $rank)
                    ->getQuery()
                    ->execute();
            }

            $em->persist($Category);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();var_dump($e);

            return false;
        }

        return true;
    }

    /**
     * @param  \Eccube\Entity\Category $Category
     * @return bool
     */
    public function delete(\Eccube\Entity\Category $Category)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            if ($Category->getChildren()->count() > 0 || $Category->getProductCategories()->count() > 0) {
                throw new \Exception();
            }

            $rank = $Category->getRank();

            $em->createQueryBuilder()
                ->update('Eccube\Entity\Category', 'c')
                ->set('c.rank', 'c.rank - 1')
                ->where('c.rank > :rank')
                ->setParameter('rank', $rank)
                ->getQuery()
                ->execute();

            $Category->setDelFlg(1);
            $em->persist($Category);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();

            return false;
        }

        return true;
    }
}

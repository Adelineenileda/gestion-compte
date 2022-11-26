<?php

namespace AppBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;

/**
 * MembershipRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MembershipRepository extends \Doctrine\ORM\EntityRepository
{

    public function findWithNewCycleStarting($date = null)
    {
        if (!($date)) {
            $date = new \Datetime('now');
        }

        $qb = $this->createQueryBuilder('u');

        $qb
            ->where('u.withdrawn = 0')
            ->andWhere('u.firstShiftDate is not NULL')
            ->andWhere('u.firstShiftDate != :now')
            ->andWhere('MOD(DATE_DIFF(:now, u.firstShiftDate), 28) = 0')
            ->setParameter('now', $date);

        return $qb
            ->getQuery()
            ->getResult();
    }

    public function findWithHalfCyclePast($date = null)
    {
        if (!($date)) {
            $date = new \Datetime('now');
        }
        $qb = $this->createQueryBuilder('u');

        $qb
            ->where('u.withdrawn = 0')
            ->andWhere('u.frozen = 0')
            ->andWhere('u.firstShiftDate is not NULL')
            ->andWhere('MOD(DATE_DIFF(:now, u.firstShiftDate), 14) = 0')
            ->andWhere('MOD(DATE_DIFF(:now, u.firstShiftDate), 28) != 0')
            ->setParameter('now', $date);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $role
     *
     * @return array
     */
    public function findByRole($role)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
            ->from($this->_entityName, 'u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"' . $role . '"%');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \DateTime $from
     * @param int $delay
     *
     * @return array
     */
    public function findWithExpiredRegistrationFrom($from)
    {
        $qb = $this->createQueryBuilder('m');
        $qb = $qb->leftJoin("m.registrations", "r")->addSelect("r"); //registrations
        $qb = $qb->leftJoin("m.registrations", "lr", Join::WITH,'lr.date > r.date')
            ->addSelect("lr")
            ->where('lr.id IS NULL') //registration is the last one registered
            ->andWhere('m.withdrawn = false')
            ->andWhere("r.date <= :from")
            ->setParameter('from', $from);

        return $qb->getQuery()->getResult();
    }


}

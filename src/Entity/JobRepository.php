<?php
namespace Werkint\Bundle\QueueBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * @see Job
 */
class JobRepository extends EntityRepository
{
    /**
     * @param int|null $amount
     * @return Job[]
     */
    public function getLastActive($amount = null)
    {
        return $this->findBy([
            'active' => true,
        ], [
            'createdAt' => 'desc',
        ], $amount);
    }
}

<?php
namespace Werkint\Bundle\QueueBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Emisser\Bundle\CoreBundle\Service\Transactional\Annotation\Transactional;
use Werkint\Bundle\MutexBundle\Service\SemLock\Annotation\SemLock;
use Werkint\Bundle\MutexBundle\Service\SemLock\SemLockAwareInterface;
use Werkint\Bundle\QueueBundle\Entity\Job;

/**
 * TODO: write "JobRunner" info
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class JobRunner implements
    SemLockAwareInterface
{
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    private $processed = [];

    /**
     * @param Job $job
     * @return boolean|int
     *
     * @SemLock(key="='werkint_queue.job.'~job.getId()")
     * @Transactional(onError="processOnError")
     */
    public function run(Job $job)
    {
        $processor = $this->getProcessor($job->getClass());

        if ($processor->isMergeable($job)) {
            if ($this->jobAlreadyStarted($job)) {
                $this->setProcessed($job);
                return -1;
            }

            $this->processed[] = $job;
        }

        $result = $processor->run($job);

        $this->setProcessed($job);

        return $result;
    }

    private function setProcessed(Job $job)
    {
        $job->setProcessedAt(new \DateTime())
            ->setActive(false);
        $this->entityManager->flush();
    }

    /**
     * @param Job $job
     * @return bool
     */
    private function jobAlreadyStarted(Job $job)
    {
        foreach ($this->processed as $jobProcessed) {
            if ($job->isSameAs($jobProcessed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $class
     * @return JobProcessorInterface
     */
    private function getProcessor($class)
    {
        return $this->processors[$class];
    }

    /**
     * @var array|JobProcessorInterface[]
     */
    private $processors;

    /**
     * @param JobProcessorInterface $processor
     * @param string                $class
     * @throws \Exception
     */
    public function addProcessor(JobProcessorInterface $processor, $class)
    {
        if (!$processor->isSupported($class)) {
            throw new \Exception(sprintf('Class not supported: %s', $class));
        }

        $this->processors[$class] = $processor;
    }
}
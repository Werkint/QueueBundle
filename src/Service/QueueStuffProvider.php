<?php
namespace Werkint\Bundle\QueueBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Werkint\Bundle\CommandBundle\Service\Processor\Stuff\StuffProviderInterface;
use Werkint\Bundle\FrameworkExtraBundle\Service\Logger\IndentedLoggerInterface;
use Werkint\Bundle\LogBundle\Service\Logger\Annotation\LoggerAware;
use Werkint\Bundle\LogBundle\Service\Logger\LoggerAwareInterface;
use Werkint\Bundle\MutexBundle\Service\MutexManagerInterface;
use Werkint\Bundle\QueueBundle\Entity\Job;
use Werkint\Bundle\QueueBundle\Entity\JobRepository;

/**
 * Запускает очередь
 * TODO: remove
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class QueueStuffProvider implements
    StuffProviderInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $em;
    protected $repoJob;
    protected $jobRunner;
    protected $mutexManager;

    public function __construct(
        EntityManagerInterface $em,
        JobRepository $repoJob,
        JobRunner $jobRunner,
        MutexManagerInterface $mutexManager
    ) {
        $this->em = $em;
        $this->repoJob = $repoJob;
        $this->jobRunner = $jobRunner;
        $this->mutexManager = $mutexManager;
    }

    /**
     * @inheritdoc
     *
     * @LoggerAware()
     */
    public function process(
        IndentedLoggerInterface $output,
        ContainerAwareCommand $command = null
    ) {
        $this->logger->debug('Processing queue');
        $output->write('Processing queue... ');

        $jobs = $this->repoJob->getLastActive(50);
        $this->toggleJobsLocks($jobs, true);

        $amount = 0;
        $skipped = 0;
        $failed = 0;
        foreach ($jobs as $job) {
            try {
                $this->em->refresh($job);
                if (!$job->isActive()) {
                    continue;
                }

                $result = $this->processJob($job);
                if ($result === true) {
                    $amount++;
                } elseif ($result === -1) {
                    $skipped++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $output->write('finished with exception. ');

                break;
            }
        }

        $this->toggleJobsLocks($jobs, false);

        $output->writeln(sprintf('%s processed, %s skipped, %s failed',
            (string)$amount,
            (string)$skipped,
            (string)$failed
        ));
        $this->logger->debug('finished');

        if (isset($e)) {
            throw $e;
        }
    }

    /**
     * @param array|Job[] $jobs
     * @param boolean     $toggle
     */
    private function toggleJobsLocks(array $jobs, $toggle)
    {
        foreach ($jobs as $job) {
            $class = sprintf('werkint_queue.job.%s', (string)$job->getId());

            if ($toggle) {
                $this->mutexManager->lock($class);
            } else {
                $this->mutexManager->unlock($class);
            }
        }
    }

    private function processJob(Job $job)
    {
        return $this->jobRunner->run($job);
    }
}
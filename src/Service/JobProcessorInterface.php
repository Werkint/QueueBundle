<?php
namespace Werkint\Bundle\QueueBundle\Service;

use Werkint\Bundle\QueueBundle\Entity\Job;

/**
 * TODO: write "JobProcessorInterface" info
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
interface JobProcessorInterface
{
    /**
     * @param string $class
     * @return boolean
     */
    public function isSupported($class);

    /**
     * @param string $class
     * @return boolean
     */
    public function isMergeable($class);

    /**
     * @param Job $job
     * @return boolean
     */
    public function run(Job $job);
}
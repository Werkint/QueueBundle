<?php
namespace Werkint\Bundle\QueueBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Werkint\Bundle\QueueBundle\DependencyInjection\Compiler\JobProcessorPass;

/**
 * WerkintQueueBundle.
 */
class WerkintQueueBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new JobProcessorPass());
    }
}

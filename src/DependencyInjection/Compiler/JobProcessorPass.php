<?php
namespace Werkint\Bundle\QueueBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * JobProcessorPass.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class JobProcessorPass implements
    CompilerPassInterface
{
    const CLASS_SRV = 'werkint_queue.jobrunner';
    const CLASS_TAG = 'werkint_queue.jobprocessor';

    /**
     * {@inheritdoc}
     */
    public function process(
        ContainerBuilder $container
    ) {
        if (!$container->hasDefinition(static::CLASS_SRV)) {
            return;
        }
        $definition = $container->getDefinition(
            static::CLASS_SRV
        );

        $list = $container->findTaggedServiceIds(static::CLASS_TAG);
        foreach ($list as $id => $attributes) {
            $a = $attributes[0];
            $definition->addMethodCall(
                'addProcessor', [
                    new Reference($id),
                    $a['class'],
                ]
            );
        }
    }

}

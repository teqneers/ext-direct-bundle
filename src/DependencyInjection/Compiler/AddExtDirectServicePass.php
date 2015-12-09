<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 09.12.15
 * Time: 15:21
 */

namespace TQ\Bundle\ExtDirectBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class AddExtDirectServicePass
 *
 * @package TQ\Bundle\ExtDirectBundle\DependencyInjection\Compiler
 */
class AddExtDirectServicePass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tagName = 'tq_extdirect.service';

        $classes = [];
        foreach ($container->findTaggedServiceIds($tagName) as $serviceId => $arguments) {
            $arguments = reset($arguments);
            if (isset($arguments['endpoint'])) {
                $serviceDefinition = $container->getDefinition($serviceId);

                $classes[$arguments['endpoint']][] = $serviceDefinition->getClass();
            }
        }

        $classAnnotationDriverIdTemplate = 'tq_extdirect.endpoint.%s.metadata.class_annotation_driver';
        foreach ($classes as $endpoint => $endPointClasses) {
            $classAnnotationDriverId = sprintf($classAnnotationDriverIdTemplate, $endpoint);
            if (!$container->hasDefinition($classAnnotationDriverId)) {
                continue;
            }

            $classAnnotationDriverDefinition = $container->getDefinition($classAnnotationDriverId);
            $classAnnotationDriverDefinition->addMethodCall('addClasses', array($endPointClasses));
        }
    }
}

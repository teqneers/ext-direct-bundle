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
        foreach ($container->findTaggedServiceIds($tagName) as $serviceId => $tags) {
            $serviceDefinition = $container->getDefinition($serviceId);
            foreach ($tags as $tag) {
                if (isset($tag['endpoint'])) {
                    $classes[$tag['endpoint']][$serviceDefinition->getClass()] = $serviceId;
                }
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

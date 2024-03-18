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
    public function process(ContainerBuilder $container): void
    {
        $tagName         = 'tq_extdirect.service';
        $defaultEndpoint = $container->getParameter('tq_extdirect.endpoint.default');

        $classes = [];
        foreach ($container->findTaggedServiceIds($tagName) as $serviceId => $tags) {
            $serviceDefinition = $container->getDefinition($serviceId);
            foreach ($tags as $tag) {
                $endpoint = isset($tag['endpoint']) ? $tag['endpoint'] : $defaultEndpoint;
                if ($endpoint) {
                    $alias = isset($tag['alias']) ? $tag['alias'] : null;

                    $classes[$endpoint][$serviceDefinition->getClass()] = [$serviceId, $alias];

                    // ensures that services are public
                    $serviceDefinition->setPublic(true);
                }
            }
        }

        $registryIdTemplate = 'tq_extdirect.endpoint.%s.registry';
        foreach ($classes as $endpoint => $endPointClasses) {
            $registryId = sprintf($registryIdTemplate, $endpoint);
            if (!$container->hasDefinition($registryId)) {
                continue;
            }

            $registry = $container->getDefinition($registryId);
            $registry->addMethodCall('addServices', array($endPointClasses));
        }
    }
}

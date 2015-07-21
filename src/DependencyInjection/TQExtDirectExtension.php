<?php
/**
 * teqneers/ext-direct-bundle
 *
 * @category   TQ
 * @package    TQ\Bundle\ExtDirectBundle
 * @copyright  Copyright (C) 2015 by TEQneers GmbH & Co. KG
 */

namespace TQ\Bundle\ExtDirectBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;


/**
 * Class TQExtDirectExtension
 *
 * @package TQ\Bundle\ExtDirectBundle\DependencyInjection
 */
class TQExtDirectExtension extends Extension
{

    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('extdirect.yml');

        $configuration = $this->getConfiguration($config, $container);
        $config        = $this->processConfiguration($configuration, $config);

        $container->setParameter('tq_extdirect.debug', $config['debug']);

        if (!$config['debug']) {
            $container->removeDefinition('tq_extdirect.router.listener.stopwatch');
        }

        if ($config['cache'] === 'none') {
            $container->removeAlias('tq_extdirect.metadata.cache');
        } elseif ($config['cache'] === 'file') {
            $container->getDefinition('tq_extdirect.metadata.cache.file')
                      ->replaceArgument(0, $config['file_cache_dir']);

            $dir = $container->getParameterBag()
                             ->resolveValue($config['file_cache_dir']);
            if (!file_exists($dir)) {
                if (!@mkdir($dir, 0777, true)) {
                    throw new \RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
                }
            }
        } else {
            $container->setAlias('tq_extdirect.metadata.cache', new Alias($config['cache'], false));
        }

        $container->getDefinition('tq_extdirect.router.argument_validator')
                  ->replaceArgument(1, $config['strict_validation']);
        if (!$config['validate_arguments']) {
            $container->removeDefinition('tq_extdirect.router.listener.argument_validation');
        }

        if (!$config['convert_arguments']) {
            $container->removeDefinition('tq_extdirect.router.listener.argument_conversion');
        }

        if (!$config['convert_result']) {
            $container->removeDefinition('tq_extdirect.router.listener.result_conversion');
        }

        foreach ($config['endpoints'] as $id => $endpoint) {
            $endpoint['id'] = $id;
            $this->loadEndpoints($endpoint, $container);
        }
    }

    /**
     * @param array            $endpoint
     * @param ContainerBuilder $container
     */
    private function loadEndpoints(array $endpoint, ContainerBuilder $container)
    {
        $id          = $endpoint['id'];
        $directories = array();
        if ($endpoint['auto_discover']) {
            $bundles = $container->getParameter('kernel.bundles');
            if ($endpoint['all_bundles']) {
                $endpoint['bundles'] = array_keys($bundles);
            }
            foreach ($endpoint['bundles'] as $endpointBundle) {
                if (!isset($bundles[$endpointBundle])) {
                    throw new \InvalidArgumentException('Bundle "' . $endpointBundle . '" is not registered"');
                }
                $ref       = new \ReflectionClass($bundles[$endpointBundle]);
                $directory = dirname($ref->getFileName()) . '/ExtDirect';
                if (is_dir($directory)) {
                    $directories[] = $directory;
                }
            }
        }
        foreach ($endpoint['directories'] as $endpointDirectory) {
            if (!is_dir($endpointDirectory)) {
                throw new \InvalidArgumentException('Directory "' . $endpointDirectory . '" is not valid"');
            }
            $directories[] = $endpointDirectory;
        }

        $annotationDriverId = sprintf('tq_extdirect.endpoint.%s.metadata.annotation_driver', $id);
        $container->setDefinition(
            $annotationDriverId,
            new DefinitionDecorator('tq_extdirect.metadata.annotation_driver')
        )
                  ->replaceArgument(1, $directories);

        $metadataFactoryId = sprintf('tq_extdirect.endpoint.%s.metadata.metadata_factory', $id);
        $container->setDefinition(
            $metadataFactoryId,
            new DefinitionDecorator('tq_extdirect.metadata_factory')
        )
                  ->replaceArgument(0, new Reference($annotationDriverId));

        $serviceLocatorId = sprintf('tq_extdirect.endpoint.%s.locator', $id);
        $container->setDefinition(
            $serviceLocatorId,
            new DefinitionDecorator('tq_extdirect.service_locator')
        )
                  ->replaceArgument(0, new Reference($metadataFactoryId));

        $descriptionFactoryId = sprintf('tq_extdirect.endpoint.%s.description_factory', $id);
        $container->setDefinition(
            $descriptionFactoryId,
            new DefinitionDecorator('tq_extdirect.service_description_factory')
        )
                  ->replaceArgument(0, new Reference($serviceLocatorId))
                  ->replaceArgument(2, $endpoint['namespace']);

        $serviceResolverId = sprintf('tq_extdirect.endpoint.%s.service_resolver', $id);
        $container->setDefinition(
            $serviceResolverId,
            new DefinitionDecorator('tq_extdirect.service_resolver')
        )
                  ->replaceArgument(0, new Reference($serviceLocatorId));

        $routerId = sprintf('tq_extdirect.endpoint.%s.router', $id);
        $container->setDefinition(
            $routerId,
            new DefinitionDecorator('tq_extdirect.router')
        )
                  ->replaceArgument(0, new Reference($serviceResolverId));

        $endpointId = sprintf('tq_extdirect.endpoint.%s', $id);
        $container->setDefinition(
            $endpointId,
            new DefinitionDecorator('tq_extdirect.endpoint')
        )
                  ->replaceArgument(0, $id)
                  ->replaceArgument(1, new Reference($descriptionFactoryId))
                  ->replaceArgument(2, new Reference($routerId))
                  ->replaceArgument(4, $endpoint['descriptor'])
                  ->setPublic(false);

        $container->getDefinition('tq_extdirect.endpoint_manager')
                  ->addMethodCall('addEndpoint', array(new Reference($endpointId)));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameterBag()
                                           ->resolveValue('%kernel.debug%'));
    }
}

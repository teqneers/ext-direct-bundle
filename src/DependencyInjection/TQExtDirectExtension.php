<?php
/**
 * teqneers/ext-direct-bundle
 *
 * @category   TQ
 * @package    TQ\Bundle\ExtDirectBundle
 * @copyright  Copyright (C) 2015 by TEQneers GmbH & Co. KG
 */

namespace TQ\Bundle\ExtDirectBundle\DependencyInjection;


use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;


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
    public function load(array $config, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('extdirect.yml');

        $configuration = $this->getConfiguration($config, $container);
        $config        = $this->processConfiguration($configuration, $config);

        $container->setParameter('tq_extdirect.debug', $config['debug']);

        if (!$config['debug']) {
            $container->removeDefinition('tq_extdirect.router.listener.stopwatch');
        } elseif (\PHP_VERSION_ID < 70000) {
            $this->addClassesToCompile(
                [
                    'TQ\ExtDirect\Router\EventListener\StopwatchListener',
                ]
            );
        }

        if (!$config['debug'] || !$container->getParameter('kernel.debug')) {
            $container->removeDefinition('tq_extdirect.data_collector.request_collector');
            $container->removeDefinition('tq_extdirect.router.request_logger');
            $container->removeDefinition('tq_extdirect.router.listener.request_log');
            $container->removeDefinition('tq_extdirect.router.listener.dump');
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

        if (!$config['validate_arguments']) {
            $container->removeDefinition('tq_extdirect.router.argument_validator');
            $container->removeDefinition('tq_extdirect.router.listener.argument_validation');
        } else {
            $container->getDefinition('tq_extdirect.router.argument_validator')
                      ->replaceArgument(1, $config['strict_validation']);
            if (\PHP_VERSION_ID < 70000) {
                $this->addClassesToCompile(
                    [
                        'TQ\ExtDirect\Router\EventListener\ArgumentValidationListener',
                    ]
                );
            }
        }

        if (!$config['convert_arguments']) {
            $container->removeDefinition('tq_extdirect.router.argument_converter');
            $container->removeDefinition('tq_extdirect.router.listener.argument_conversion');
        } elseif (\PHP_VERSION_ID < 70000) {
            $this->addClassesToCompile(
                [
                    'TQ\ExtDirect\Router\EventListener\ArgumentConversionListener',
                ]
            );
        }

        if (!$config['enable_authorization']
            || !class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')
            || !class_exists('Symfony\Component\Security\Core\Authorization\ExpressionLanguage')
        ) {
            $container->removeDefinition('tq_extdirect.router.authorization_checker');
            $container->removeDefinition('tq_extdirect.router.listener.authorization');
        } elseif (\PHP_VERSION_ID < 70000) {
            $this->addClassesToCompile(
                [
                    'TQ\ExtDirect\Router\EventListener\AuthorizationListener',
                ]
            );
        }

        if (!$config['convert_result']) {
            $container->removeDefinition('tq_extdirect.router.result_converter');
            $container->removeDefinition('tq_extdirect.router.listener.result_conversion');
        } elseif (\PHP_VERSION_ID < 70000) {
            $this->addClassesToCompile(
                [
                    'TQ\ExtDirect\Router\EventListener\ResultConversionListener',
                ]
            );
        }

        $defaultEndpoint = $config['default_endpoint'];
        $endpoints       = [];
        foreach ($config['endpoints'] as $id => $endpoint) {
            $endpoint['id'] = $id;
            $this->loadEndpoints($endpoint, $container);
            $endpoints[] = $id;
        }
        if (!$defaultEndpoint || !in_array($defaultEndpoint, $endpoints)) {
            $defaultEndpoint = reset($endpoints);
        }
        $container->setParameter('tq_extdirect.endpoint.default', $defaultEndpoint);

        if (\PHP_VERSION_ID < 70000) {
            $this->addClassesToCompile(
                [
                    'TQ\ExtDirect\Metadata\Driver\AnnotationDriver',
                    'TQ\ExtDirect\Metadata\ActionMetadata',
                    'TQ\ExtDirect\Metadata\MethodMetadata',
                    'TQ\ExtDirect\Description\ActionDescription',
                    'TQ\ExtDirect\Description\MethodDescription',
                    'TQ\ExtDirect\Description\ServiceDescription',
                    'TQ\ExtDirect\Description\ServiceDescriptionFactory',
                    'TQ\ExtDirect\Service\DefaultServiceRegistry',
                    'TQ\ExtDirect\Service\Endpoint',
                    'TQ\ExtDirect\Router\ServiceReference',
                ]
            );
        }
    }

    /**
     * @param array            $endpoint
     * @param ContainerBuilder $container
     */
    private function loadEndpoints(array $endpoint, ContainerBuilder $container)
    {
        $id          = $endpoint['id'];
        $directories = [];
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

        $pathLoaderId = sprintf('tq_extdirect.endpoint.%s.service_path_loader', $id);
        $container->setDefinition(
            $pathLoaderId,
            new ChildDefinition('tq_extdirect.service_path_loader')
        )
                  ->replaceArgument(0, $directories);

        $serviceRegistryId = sprintf('tq_extdirect.endpoint.%s.registry', $id);
        $container->setDefinition(
            $serviceRegistryId,
            new ChildDefinition('tq_extdirect.service_registry')
        )
                  ->addMethodCall('importServices', [new Reference($pathLoaderId)]);

        $descriptionFactoryId = sprintf('tq_extdirect.endpoint.%s.description_factory', $id);
        $container->setDefinition(
            $descriptionFactoryId,
            new ChildDefinition('tq_extdirect.service_description_factory')
        )
                  ->replaceArgument(0, new Reference($serviceRegistryId))
                  ->replaceArgument(1, $endpoint['namespace'])
                  ->replaceArgument(2, $endpoint['enable_buffer'])
                  ->replaceArgument(3, $endpoint['buffer_limit'])
                  ->replaceArgument(4, $endpoint['timeout'])
                  ->replaceArgument(5, $endpoint['max_retries']);

        $serviceResolverId = sprintf('tq_extdirect.endpoint.%s.service_resolver', $id);
        $container->setDefinition(
            $serviceResolverId,
            new ChildDefinition('tq_extdirect.service_resolver')
        )
                  ->replaceArgument(0, new Reference($serviceRegistryId));

        $routerId = sprintf('tq_extdirect.endpoint.%s.router', $id);
        $container->setDefinition(
            $routerId,
            new ChildDefinition('tq_extdirect.router')
        )
                  ->replaceArgument(0, new Reference($serviceResolverId));

        $endpointId = sprintf('tq_extdirect.endpoint.%s', $id);
        $container->setDefinition(
            $endpointId,
            new ChildDefinition('tq_extdirect.endpoint')
        )
                  ->replaceArgument(0, $id)
                  ->replaceArgument(1, new Reference($descriptionFactoryId))
                  ->replaceArgument(2, new Reference($routerId))
                  ->replaceArgument(4, $endpoint['descriptor'])
                  ->setPublic(false);

        $container->getDefinition('tq_extdirect.endpoint_manager')
                  ->addMethodCall('addEndpoint', [new Reference($endpointId)]);
    }

    /**
     * @return ConfigurationInterface|null
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration(
            $container->getParameterBag()
                      ->resolveValue('%kernel.debug%')
        );
    }
}

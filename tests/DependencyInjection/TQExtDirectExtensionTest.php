<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 27.07.15
 * Time: 12:52
 */

namespace TQ\Bundle\ExtDirectBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use TQ\Bundle\ExtDirectBundle\TQExtDirectBundle;

/**
 * Class TQExtDirectExtensionTest
 *
 * @package TQ\Bundle\ExtDirectBundle\Tests\DependencyInjection
 */
class TQExtDirectExtensionTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->clearTempDir();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->clearTempDir();
    }

    public function testLoadSingleDefaultEndpoint()
    {
        $container = $this->getContainerForConfig(array(
            array(
                'endpoints' => array('api' => null)
            )
        ), 'dev', true);

        /** @var \TQ\ExtDirect\Service\EndpointManager $endpointManager */
        $endpointManager = $container->get('tq_extdirect.endpoint_manager');
        $this->assertInstanceOf(
            'TQ\ExtDirect\Service\EndpointManager',
            $endpointManager
        );

        $endpoint = $endpointManager->getEndpoint('api');
        $this->assertInstanceOf(
            'TQ\ExtDirect\Service\Endpoint',
            $endpoint
        );

        $this->assertEquals('api', $endpoint->getId());
        $this->assertEquals('Ext.app.REMOTING_API', $endpoint->getDescriptor());
    }

    public function testLoaMultipleDefaultEndpoints()
    {
        $container = $this->getContainerForConfig(array(
            array(
                'endpoints' => array(
                    'api1' => array(
                        'descriptor' => 'Ext.app.REMOTING_API1'
                    ),
                    'api2' => array(
                        'descriptor' => 'Ext.app.REMOTING_API2'
                    ),
                )
            )
        ), 'dev', true);

        /** @var \TQ\ExtDirect\Service\EndpointManager $endpointManager */
        $endpointManager = $container->get('tq_extdirect.endpoint_manager');
        $this->assertInstanceOf(
            'TQ\ExtDirect\Service\EndpointManager',
            $endpointManager
        );

        $endpoint1 = $endpointManager->getEndpoint('api1');
        $endpoint2 = $endpointManager->getEndpoint('api2');
        $this->assertInstanceOf(
            'TQ\ExtDirect\Service\Endpoint',
            $endpoint1
        );
        $this->assertInstanceOf(
            'TQ\ExtDirect\Service\Endpoint',
            $endpoint2
        );

        $this->assertEquals('api1', $endpoint1->getId());
        $this->assertEquals('api2', $endpoint2->getId());
        $this->assertEquals('Ext.app.REMOTING_API1', $endpoint1->getDescriptor());
        $this->assertEquals('Ext.app.REMOTING_API2', $endpoint2->getDescriptor());
    }

    /**
     * @param array  $configs
     * @param string $environment
     * @param bool   $debug
     * @return ContainerBuilder
     */
    protected function getContainerForConfig(array $configs, $environment, $debug)
    {
        $rootPath = sys_get_temp_dir() . '/ext-direct-bundle';

        /** @var UrlGeneratorInterface */
        $urlGenerator = $this->createPartialMock(
            'Symfony\Component\Routing\Generator\UrlGeneratorInterface',
            array('generate', 'setContext', 'getContext')
        );

        /** @var \Doctrine\Common\Annotations\Reader $annotationReader */
        $annotationReader = $this->createPartialMock(
            'Doctrine\Common\Annotations\Reader',
            array(
                'getClassAnnotations',
                'getClassAnnotation',
                'getMethodAnnotations',
                'getMethodAnnotation',
                'getPropertyAnnotations',
                'getPropertyAnnotation'
            )
        );

        /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
        $validator = $this->createPartialMock(
            'Symfony\Component\Validator\Validator\ValidatorInterface',
            array(
                'validate',
                'validateProperty',
                'validatePropertyValue',
                'startContext',
                'inContext',
                'getMetadataFor',
                'hasMetadataFor'
            )
        );

        /** @var \JMS\Serializer\SerializerInterface $serializer */
        $serializer = $this->createMock('JMS\Serializer\SerializerInterface');

        $bundle    = new TQExtDirectBundle();
        $extension = $bundle->getContainerExtension();
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', $debug);
        $container->setParameter('kernel.environment', $environment);
        $container->setParameter('kernel.project_dir', $rootPath . '/app');
        $container->setParameter('kernel.cache_dir', $rootPath . '/cache');
        $container->setParameter('kernel.bundles', array());
        $container->set('router', $urlGenerator);
        $container->set('annotation_reader', $annotationReader);
        $container->set('validator', $validator);
        $container->set('jms_serializer', $serializer);
        $container->registerExtension($extension);
        $extension->load($configs, $container);
        $bundle->build($container);
        $container->compile();
        return $container;
    }


    protected function clearTempDir()
    {
        $dir = sys_get_temp_dir() . '/ext-direct-bundle';
        if (is_dir($dir)) {
            foreach (
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        $dir,
                        \RecursiveDirectoryIterator::SKIP_DOTS
                    ),
                    \RecursiveIteratorIterator::CHILD_FIRST
                ) as $file
            ) {
                /** @var \SplFileInfo $file */
                if ($file->isDir()) {
                    @rmdir($file->getPathname());
                } else {
                    @unlink($file->getPathName());
                }
            }
            @rmdir($dir);
        }
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 27.07.15
 * Time: 12:41
 */

namespace TQ\Bundle\ExtDirectBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use TQ\Bundle\ExtDirectBundle\DependencyInjection\Configuration;

/**
 * Class ConfigurationTest
 *
 * @package TQ\Bundle\ExtDirectBundle\Tests\DependencyInjection
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param bool $debug
     * @return Configuration
     */
    protected function getConfiguration($debug)
    {
        return new Configuration($debug);
    }

    public function testConfigurationDefaults()
    {
        $configuration = $this->getConfiguration(false);
        $processor     = new Processor();
        $config        = $processor->processConfiguration($configuration, array(
            array(
                'endpoints' => array(
                    'api' => null
                )
            )
        ));

        $this->assertEquals(
            array(
                'debug'              => false,
                'cache'              => 'file',
                'file_cache_dir'     => '%kernel.cache_dir%/tq_ext_direct',
                'validate_arguments' => true,
                'strict_validation'  => true,
                'convert_arguments'  => true,
                'convert_result'     => true,
                'default_endpoint'   => null,
                'endpoints'          => array(
                    'api' => array(
                        'descriptor'    => 'Ext.app.REMOTING_API',
                        'namespace'     => 'Ext.global',
                        'auto_discover' => true,
                        'all_bundles'   => true,
                        'bundles'       => array(),
                        'directories'   => array()
                    )
                )
            ),
            $config
        );
    }

    public function testConfigurationDefaultsInDebug()
    {
        $configuration = $this->getConfiguration(true);
        $processor     = new Processor();
        $config        = $processor->processConfiguration($configuration, array(
            array(
                'endpoints' => array(
                    'api' => null
                )
            )
        ));

        $this->assertEquals(
            array(
                'debug'              => true,
                'cache'              => 'file',
                'file_cache_dir'     => '%kernel.cache_dir%/tq_ext_direct',
                'validate_arguments' => true,
                'strict_validation'  => true,
                'convert_arguments'  => true,
                'convert_result'     => true,
                'default_endpoint'   => null,
                'endpoints'          => array(
                    'api' => array(
                        'descriptor'    => 'Ext.app.REMOTING_API',
                        'namespace'     => 'Ext.global',
                        'auto_discover' => true,
                        'all_bundles'   => true,
                        'bundles'       => array(),
                        'directories'   => array()
                    )
                )
            ),
            $config
        );
    }

    public function testConfigurationInvalidIfEndpointsIsMissing()
    {
        $configuration = $this->getConfiguration(false);
        $processor     = new Processor();

        $this->setExpectedException(
            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'The child node "endpoints" at path "tq_ext_direct" must be configured.'
        );

        $processor->processConfiguration($configuration, array(array()));
    }

    public function testConfigurationInvalidIfEndpointsIsEmpty()
    {
        $configuration = $this->getConfiguration(false);
        $processor     = new Processor();

        $this->setExpectedException(
            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            'The path "tq_ext_direct.endpoints" should have at least 1 element(s) defined.'
        );

        $processor->processConfiguration($configuration, array(
            array(
                'endpoints' => array()

            )
        ));
    }
}

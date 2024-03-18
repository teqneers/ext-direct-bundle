<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 27.07.15
 * Time: 13:43
 */

namespace TQ\Bundle\ExtDirectBundle\Tests;

use JMS\SerializerBundle\JMSSerializerBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use TQ\Bundle\ExtDirectBundle\Controller\ApiController;
use TQ\Bundle\ExtDirectBundle\Controller\RouterController;
use TQ\Bundle\ExtDirectBundle\TQExtDirectBundle;

/**
 * Class TQExtDirectBundleTest
 *
 * @package TQ\Bundle\ExtDirectBundle\Tests
 */
class TQExtDirectBundleTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        if (Kernel::VERSION_ID < 40000) {
            $this->markTestSkipped('Test only supported on Symfony >= 4');
        }
        $this->clearTempDir();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->clearTempDir();
    }

    public function testServiceDescription()
    {
        $kernel = new AppKernel('prod', false);
        $kernel->boot();

        /** @var ApiController $controller */
        $controller = $kernel->getContainer()
                             ->get('tq_extdirect.ext_direct_api_controller');
        $request    = new Request();
        $response   = $controller->apiAction('api', $request);

        $this->assertInstanceOf(
            'TQ\ExtDirect\Http\ServiceDescriptionResponse',
            $response
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/javascript', $response->headers->get('Content-Type'));

        $this->expectOutputString(<<<'OUT'
var Ext = Ext || {};
Ext.app = Ext.app || {};
Ext.app.REMOTING_API = {"type":"remoting","url":"\/api\/router","namespace":"Ext.global","actions":{"TQ.Bundle.ExtDirectBundle.Tests.Services.Sub.Service1":[{"name":"methodA","len":1}],"TQ.Bundle.ExtDirectBundle.Tests.Services.Service1":[{"name":"methodA","len":1}]}};
OUT
        );
        $response->prepare($request);
        $response->sendContent();
    }

    public function testRouterCall()
    {
        $kernel = new AppKernel('prod', false);
        $kernel->boot();

        /** @var RouterController $controller */
        $controller = $kernel->getContainer()
                             ->get('tq_extdirect.ext_direct_router_controller');
        $request    = new Request(
            array(),
            array(),
            array(),
            array(),
            array(),
            array(),
            '{"action":"TQ.Bundle.ExtDirectBundle.Tests.Services.Service1","method":"methodA","data":["a"],"type":"rpc","tid":1}'
        );
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('Content-Type', 'application/json');
        $response = $controller->routerAction('api', $request);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $response
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $this->expectOutputString(<<<'OUT'
{"type":"rpc","tid":1,"action":"TQ.Bundle.ExtDirectBundle.Tests.Services.Service1","method":"methodA","result":"a"}
OUT
        );
        $response->prepare($request);
        $response->sendContent();
    }

    public function testRouterCallServiceAddedViaTag()
    {
        $kernel = new AppKernel('prod', false);
        $kernel->boot();

        /** @var RouterController $controller */
        $controller = $kernel->getContainer()
                             ->get('tq_extdirect.ext_direct_router_controller');
        $request    = new Request(
            array(),
            array(),
            array(),
            array(),
            array(),
            array(),
            '{"action":"TQ.Bundle.ExtDirectBundle.Tests.Services.Service1","method":"methodA","data":["a"],"type":"rpc","tid":1}'
        );
        $request->setMethod(Request::METHOD_POST);
        $request->headers->set('Content-Type', 'application/json');
        $response = $controller->routerAction('api', $request);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $response
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $this->expectOutputString(<<<'OUT'
{"type":"rpc","tid":1,"action":"TQ.Bundle.ExtDirectBundle.Tests.Services.Service1","method":"methodA","result":"a"}
OUT
        );
        $response->prepare($request);
        $response->sendContent();
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

class AppKernel extends Kernel
{
    public function registerBundles(): \Traversable|array
    {
        return array(
            new FrameworkBundle(),
            new JMSSerializerBundle(),
            new TQExtDirectBundle(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/TQExtDirectBundleTestConfig.yml');
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/ext-direct-bundle/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/ext-direct-bundle/log';
    }
}

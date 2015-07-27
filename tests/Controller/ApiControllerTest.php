<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 27.07.15
 * Time: 14:18
 */

namespace TQ\Bundle\ExtDirectBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use TQ\Bundle\ExtDirectBundle\Controller\ApiController;
use TQ\ExtDirect\Http\ServiceDescriptionResponse;

/**
 * Class ApiControllerTest
 *
 * @package TQ\Bundle\ExtDirectBundle\Tests\Controller
 */
class ApiControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testServiceDescriptionAction()
    {
        /** @var UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject $urlGenerator */
        $urlGenerator = $this->getMock(
            'Symfony\Component\Routing\Generator\UrlGeneratorInterface',
            array('generate', 'setContext', 'getContext')
        );

        $urlGenerator->expects($this->once())
                     ->method('generate')
                     ->with(
                         $this->equalTo('tq_extdirect_router'),
                         $this->equalTo(
                             array(
                                 'endpoint' => 'api'
                             )
                         )
                     )
                     ->willReturn('http://example.com/api/router');

        /** @var \TQ\ExtDirect\Service\Endpoint|\PHPUnit_Framework_MockObject_MockObject $endpoint */
        $endpoint = $this->getMock(
            'TQ\ExtDirect\Service\Endpoint',
            array('getId', 'createServiceDescription'),
            array(),
            '',
            false
        );

        $endpoint->expects($this->once())
                 ->method('getId')
                 ->willReturn('api');
        $endpoint->expects($this->once())
                 ->method('createServiceDescription')
                 ->with(
                     $this->equalTo('http://example.com/api/router'),
                     $this->equalTo('js')
                 )
                 ->willReturn(new ServiceDescriptionResponse());

        /** @var \TQ\ExtDirect\Service\EndpointManager|\PHPUnit_Framework_MockObject_MockObject $endpointManager */
        $endpointManager = $this->getMock(
            'TQ\ExtDirect\Service\EndpointManager',
            array('getEndpoint')
        );

        $endpointManager->expects($this->once())
                        ->method('getEndpoint')
                        ->with($this->equalTo('api'))
                        ->willReturn($endpoint);


        $controller = new ApiController($endpointManager, $urlGenerator);

        $request = new Request();
        $request->setMethod(Request::METHOD_GET);
        $response = $controller->apiAction('api', $request);

        $this->assertInstanceOf('TQ\ExtDirect\Http\ServiceDescriptionResponse', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/javascript', $response->headers->get('Content-Type'));
    }

    public function testServiceDescriptionActionFailsOnNonGetRequest()
    {
        /** @var UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject $urlGenerator */
        $urlGenerator = $this->getMock(
            'Symfony\Component\Routing\Generator\UrlGeneratorInterface',
            array('generate', 'setContext', 'getContext')
        );

        $urlGenerator->expects($this->never())
                     ->method('generate');

        /** @var \TQ\ExtDirect\Service\EndpointManager|\PHPUnit_Framework_MockObject_MockObject $endpointManager */
        $endpointManager = $this->getMock(
            'TQ\ExtDirect\Service\EndpointManager',
            array('getEndpoint')
        );

        $endpointManager->expects($this->never())
                        ->method('getEndpoint');

        $controller = new ApiController($endpointManager, $urlGenerator);

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException');
        $controller->apiAction('api', $request);
    }

    public function testServiceDescriptionActionFailsWhenEndpointIsNotFound()
    {
        /** @var UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject $urlGenerator */
        $urlGenerator = $this->getMock(
            'Symfony\Component\Routing\Generator\UrlGeneratorInterface',
            array('generate', 'setContext', 'getContext')
        );

        $urlGenerator->expects($this->never())
                     ->method('generate');

        /** @var \TQ\ExtDirect\Service\EndpointManager|\PHPUnit_Framework_MockObject_MockObject $endpointManager */
        $endpointManager = $this->getMock(
            'TQ\ExtDirect\Service\EndpointManager',
            array('getEndpoint')
        );

        $endpointManager->expects($this->once())
                        ->method('getEndpoint')
                        ->with($this->equalTo('api'))
                        ->willThrowException(new \InvalidArgumentException('Endpoint "api" not found"'));

        $controller = new ApiController($endpointManager, $urlGenerator);

        $request = new Request();
        $request->setMethod(Request::METHOD_GET);

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $controller->apiAction('api', $request);
    }

    public function testServiceDescriptionActionInJsonFormat()
    {
        /** @var UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject $urlGenerator */
        $urlGenerator = $this->getMock(
            'Symfony\Component\Routing\Generator\UrlGeneratorInterface',
            array('generate', 'setContext', 'getContext')
        );

        $urlGenerator->expects($this->once())
                     ->method('generate')
                     ->with(
                         $this->equalTo('tq_extdirect_router'),
                         $this->equalTo(
                             array(
                                 'endpoint' => 'api'
                             )
                         )
                     )
                     ->willReturn('http://example.com/api/router');

        /** @var \TQ\ExtDirect\Service\Endpoint|\PHPUnit_Framework_MockObject_MockObject $endpoint */
        $endpoint = $this->getMock(
            'TQ\ExtDirect\Service\Endpoint',
            array('getId', 'createServiceDescription'),
            array(),
            '',
            false
        );

        $endpoint->expects($this->once())
                 ->method('getId')
                 ->willReturn('api');
        $endpoint->expects($this->once())
                 ->method('createServiceDescription')
                 ->with(
                     $this->equalTo('http://example.com/api/router'),
                     $this->equalTo('json')
                 );

        /** @var \TQ\ExtDirect\Service\EndpointManager|\PHPUnit_Framework_MockObject_MockObject $endpointManager */
        $endpointManager = $this->getMock(
            'TQ\ExtDirect\Service\EndpointManager',
            array('getEndpoint')
        );

        $endpointManager->expects($this->once())
                        ->method('getEndpoint')
                        ->with($this->equalTo('api'))
                        ->willReturn($endpoint);


        $controller = new ApiController($endpointManager, $urlGenerator);

        $request = new Request();
        $request->setMethod(Request::METHOD_GET);
        $request->setRequestFormat('json');
        $controller->apiAction('api', $request);
    }
}

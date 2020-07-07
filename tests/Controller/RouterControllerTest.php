<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 27.07.15
 * Time: 14:18
 */

namespace TQ\Bundle\ExtDirectBundle\Tests\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use TQ\Bundle\ExtDirectBundle\Controller\RouterController;

/**
 * Class RouterControllerTest
 *
 * @package TQ\Bundle\ExtDirectBundle\Tests\Controller
 */
class RouterControllerTest extends TestCase
{
    public function testRouterAction()
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        /** @var \TQ\ExtDirect\Service\Endpoint|MockObject $endpoint */
        $endpoint = $this->createPartialMock(
            'TQ\ExtDirect\Service\Endpoint',
            array('getId', 'handleRequest')
        );

        $endpoint->expects($this->once())
                 ->method('handleRequest')
                 ->with($this->equalTo($request))
                 ->willReturn(new JsonResponse());

        /** @var \TQ\ExtDirect\Service\EndpointManager|MockObject $endpointManager */
        $endpointManager = $this->createPartialMock(
            'TQ\ExtDirect\Service\EndpointManager',
            array('getEndpoint')
        );

        $endpointManager->expects($this->once())
                        ->method('getEndpoint')
                        ->with($this->equalTo('api'))
                        ->willReturn($endpoint);


        $controller = new RouterController($endpointManager);
        $response   = $controller->routerAction('api', $request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testRouterActionFailsOnNonPostRequest()
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_GET);

        /** @var \TQ\ExtDirect\Service\EndpointManager|MockObject $endpointManager */
        $endpointManager = $this->createPartialMock(
            'TQ\ExtDirect\Service\EndpointManager',
            array('getEndpoint')
        );

        $endpointManager->expects($this->never())
                        ->method('getEndpoint');

        $controller = new RouterController($endpointManager);
        $this->expectException('Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException');
        $controller->routerAction('api', $request);
    }

    public function testRouterActionFailsWhenEndpointIsNotFound()
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        /** @var \TQ\ExtDirect\Service\EndpointManager|MockObject $endpointManager */
        $endpointManager = $this->createPartialMock(
            'TQ\ExtDirect\Service\EndpointManager',
            array('getEndpoint')
        );

        $endpointManager->expects($this->once())
                        ->method('getEndpoint')
                        ->with($this->equalTo('api'))
                        ->willThrowException(new \InvalidArgumentException('Endpoint "api" not found"'));

        $controller = new RouterController($endpointManager);
        $this->expectException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $controller->routerAction('api', $request);
    }
}

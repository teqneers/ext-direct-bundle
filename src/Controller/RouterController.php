<?php
/**
 * teqneers/ext-direct-bundle
 *
 * @category   TQ
 * @package    TQ\Bundle\ExtDirectBundle
 * @copyright  Copyright (C) 2015 by TEQneers GmbH & Co. KG
 */

namespace TQ\Bundle\ExtDirectBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TQ\ExtDirect\Router\Exception\BadRequestException;
use TQ\ExtDirect\Service\EndpointManager;

/**
 * Class RouterController
 *
 * @package TQ\Bundle\ExtDirectBundle\Controller
 */
class RouterController
{
    /**
     * @var EndpointManager
     */
    private $endpointManager;

    /**
     * @param EndpointManager $endpointManager
     */
    public function __construct(EndpointManager $endpointManager)
    {
        $this->endpointManager = $endpointManager;
    }

    /**
     * @param string  $endpoint
     * @param Request $request
     * @return Response
     */
    public function routerAction($endpoint, Request $request)
    {
        $request->setRequestFormat($request->getContentTypeFormat());

        if ($request->getMethod() !== Request::METHOD_POST) {
            throw new MethodNotAllowedHttpException(array(Request::METHOD_POST));
        }

        try {
            $endpoint = $this->endpointManager->getEndpoint($endpoint);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException('Not Found', $e);
        }

        try {
            return $endpoint->handleRequest($request);
        } catch (BadRequestException $e) {
            throw new BadRequestHttpException('Bad Request', $e);
        }
    }
}

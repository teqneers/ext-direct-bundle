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
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use TQ\ExtDirect\Service\EndpointManager;

/**
 * Class ExtDirectController
 *
 * @package TQ\Bundle\ExtDirectBundle\Controller
 */
class ApiController
{
    /**
     * @var EndpointManager
     */
    private $endpointManager;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param EndpointManager       $endpointManager
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(EndpointManager $endpointManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->endpointManager = $endpointManager;
        $this->urlGenerator    = $urlGenerator;
    }

    /**
     * @param string  $endpoint
     * @param Request $request
     * @return Response
     */
    public function apiAction($endpoint, Request $request)
    {
        $session = $request->getSession();
        if ($session && $session->isStarted()) {
            $session->save();
        }

        if ($request->getMethod() !== Request::METHOD_GET) {
            throw new MethodNotAllowedHttpException(array(Request::METHOD_GET));
        }

        try {
            $endpoint = $this->endpointManager->getEndpoint($endpoint);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException('Not found', $e);
        }

        return $endpoint->createServiceDescription(
            $this->urlGenerator->generate(
                'tq_extdirect_router',
                array(
                    'endpoint' => $endpoint->getId()
                )
            ),
            $request->getRequestFormat('js')
        );
    }
}

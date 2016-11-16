<?php
/**
 * TQ\Bundle\ExtDirectBundle\Router\RequestLogListener
 *
 * @author    stefan
 * @package   TQ\Bundle\ExtDirectBundle\Router
 * @copyright Copyright (C) 2003-2016 TEQneers GmbH & Co. KG. All rights reserved.
 */

namespace TQ\Bundle\ExtDirectBundle\Router;


use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TQ\ExtDirect\Router\Event\BeginRequestEvent;
use TQ\ExtDirect\Router\Event\EndRequestEvent;
use TQ\ExtDirect\Router\RouterEvents;

/**
 * Class RequestLogListener
 *
 * @package TQ\Bundle\ExtDirectBundle\Router
 */
class RequestLogListener implements EventSubscriberInterface
{
    /**
     * @var RequestLogger
     */
    private $requestLogger;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param RequestLogger        $requestLogger
     * @param LoggerInterface|null $logger
     */
    public function __construct(RequestLogger $requestLogger, LoggerInterface $logger = null)
    {
        $this->requestLogger = $requestLogger;
        $this->logger        = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            RouterEvents::BEGIN_REQUEST => ['onBeginRequest', 255],
            RouterEvents::END_REQUEST   => ['onEndRequest', -255],
        ];
    }

    /**
     * @param BeginRequestEvent $event
     */
    public function onBeginRequest(BeginRequestEvent $event)
    {
        if ($this->logger) {
            $this->logger->debug(
                'Ext.direct request received: {request}',
                ['request' => json_encode($event->getDirectRequest())]
            );
        }
        $this->requestLogger->startRequest($event->getDirectRequest());
    }

    /**
     * @param EndRequestEvent $event
     */
    public function onEndRequest(EndRequestEvent $event)
    {
        $this->requestLogger->endRequest($event->getDirectResponse());
        if ($this->logger) {
            $this->logger->debug(
                'Ext.direct response sent: {response}',
                ['response' => json_encode($event->getDirectResponse())]
            );
        }
    }
}

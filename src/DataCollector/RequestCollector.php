<?php
/**
 * TQ\Bundle\ExtDirectBundle\DataCollector\RequestCollector
 *
 * @author    stefan
 * @package   TQ\Bundle\ExtDirectBundle\DataCollector
 * @copyright Copyright (C) 2003-2016 TEQneers GmbH & Co. KG. All rights reserved.
 */

namespace TQ\Bundle\ExtDirectBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use TQ\Bundle\ExtDirectBundle\Router\RequestLogger;

/**
 * Class RequestCollector
 *
 * @package TQ\Bundle\ExtDirectBundle\DataCollector
 */
class RequestCollector extends DataCollector
{
    /**
     * @var RequestLogger
     */
    private $requestLogger;

    /**
     * @param RequestLogger $requestLogger
     */
    public function __construct(RequestLogger $requestLogger)
    {
        $this->requestLogger = $requestLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        if (method_exists($this, 'cloneVar')) {
            $this->data['request']  = $this->cloneVar($this->requestLogger->getRequest(false));
            $this->data['response'] = $this->cloneVar($this->requestLogger->getResponse(false));
            $this->data['time']     = $this->requestLogger->getElapsedTime();
        } else {
            $this->data['request']  = $this->varToString($this->requestLogger->getRequest(false));
            $this->data['response'] = $this->varToString($this->requestLogger->getResponse(false));
            $this->data['time']     = $this->requestLogger->getElapsedTime();
        }
    }

    /**
     * @return bool
     */
    public function isExtDirectRequest()
    {
        return $this->data['request'] !== null;
    }

    /**
     * @return float
     */
    public function getTime()
    {
        return $this->data['time'];
    }

    /**
     * @return array|null
     */
    public function getRequest()
    {
        return $this->data['request'];
    }

    /**
     * @return array|null
     */
    public function getResponse()
    {
        return $this->data['response'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tq_extdirect.request_collector';
    }
}

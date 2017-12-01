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
        $extDirectRequest  = $this->requestLogger->getRequest(false);
        $extDirectResponse = $this->requestLogger->getResponse(false);

        $this->data['formPost']    = $this->requestLogger->isFormPost();
        $this->data['upload']      = $this->requestLogger->isUpload();
        $this->data['time']        = $this->requestLogger->getElapsedTime();
        $this->data['isExtDirect'] = $extDirectRequest !== null;

        $requestCount = 0;
        $requests     = [];
        if ($this->data['isExtDirect']) {
            $firstKey = key($extDirectRequest);
            if (!is_numeric($firstKey)) {
                $requestCount      = 1;
                $extDirectRequest  = [$extDirectRequest];
                $extDirectResponse = [$extDirectResponse];
            } else {
                $requestCount = count($extDirectRequest);
            }

            $hasCloneVar = method_exists($this, 'cloneVar');
            foreach ($extDirectRequest as $i => $r) {
                if ($hasCloneVar) {
                    $data        = $this->cloneVar($r['data']);
                    $extResponse = $this->cloneVar($extDirectResponse[$i]);
                } else {
                    $data        = $this->varToString($r['data']);
                    $extResponse = $this->varToString($extDirectResponse[$i]);
                }

                $requests[] = array_merge($r, ['data' => $data, 'response' => $extResponse]);
            }
        }
        $this->data['requestCount'] = $requestCount;
        $this->data['requests']     = $requests;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->data = [];
        $this->requestLogger->reset();
    }

    /**
     * @return bool
     */
    public function isExtDirectRequest()
    {
        return $this->data['isExtDirect'];
    }

    /**
     * @return bool
     */
    public function isFormPost()
    {
        return $this->data['formPost'];
    }

    /**
     * @return bool
     */
    public function isUpload()
    {
        return $this->data['upload'];
    }

    /**
     * @return float
     */
    public function getTime()
    {
        return $this->data['time'];
    }

    /**
     * @return int
     */
    public function getRequestCount()
    {
        return $this->data['requestCount'];
    }

    /**
     * @return array
     */
    public function getRequests()
    {
        return $this->data['requests'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tq_extdirect.request_collector';
    }
}

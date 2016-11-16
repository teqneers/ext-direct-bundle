<?php
/**
 * TQ\Bundle\ExtDirectBundle\DataCollector\RequestCollector
 *
 * @author    stefan
 * @package   TQ\Bundle\ExtDirectBundle\DataCollector
 * @copyright Copyright (C) 2003-2016 TEQneers GmbH & Co. KG. All rights reserved.
 */

namespace TQ\Bundle\ExtDirectBundle\DataCollector;

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
    public function getName()
    {
        return 'tq_extdirect.request_collector';
    }
}

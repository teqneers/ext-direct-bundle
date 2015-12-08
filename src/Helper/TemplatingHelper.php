<?php
/**
 * teqneers/ext-direct-bundle
 *
 * @category   TQ
 * @package    TQ\Bundle\ExtDirectBundle
 * @copyright  Copyright (C) 2015 by TEQneers GmbH & Co. KG
 */

namespace TQ\Bundle\ExtDirectBundle\Helper;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class TemplatingHelper
 *
 * @package TQ\Bundle\ExtDirectBundle\Helper
 */
class TemplatingHelper
{
    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * @param UrlGeneratorInterface $generator
     */
    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param string $endpoint
     * @param string $format
     * @return string
     */
    public function getApiPath($endpoint, $format = 'js')
    {
        return $this->generator->generate('tq_extdirect_api', array(
            'endpoint' => $endpoint,
            '_format'  => $format
        ));
    }
}

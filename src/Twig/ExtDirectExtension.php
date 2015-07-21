<?php
/**
 * teqneers/ext-direct-bundle
 *
 * @category   TQ
 * @package    TQ\Bundle\ExtDirectBundle
 * @copyright  Copyright (C) 2015 by TEQneers GmbH & Co. KG
 */

namespace TQ\Bundle\ExtDirectBundle\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ExtDirectExtension
 *
 * @package TQ\Bundle\ExtDirectBundle\Twig
 */
class ExtDirectExtension extends \Twig_Extension
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
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'extDirectApiPath',
                [$this, 'getApiPath']
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tq_extdirect_extension';
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

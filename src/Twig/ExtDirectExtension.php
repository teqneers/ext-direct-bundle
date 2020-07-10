<?php
/**
 * teqneers/ext-direct-bundle
 *
 * @category   TQ
 * @package    TQ\Bundle\ExtDirectBundle
 * @copyright  Copyright (C) 2015 by TEQneers GmbH & Co. KG
 */

namespace TQ\Bundle\ExtDirectBundle\Twig;

use TQ\Bundle\ExtDirectBundle\Helper\TemplatingHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class ExtDirectExtension
 *
 * @package TQ\Bundle\ExtDirectBundle\Twig
 */
class ExtDirectExtension extends AbstractExtension
{
    /**
     * @var TemplatingHelper
     */
    private $templatingHelper;

    /**
     * @param TemplatingHelper $templatingHelper
     */
    public function __construct(TemplatingHelper $templatingHelper)
    {
        $this->templatingHelper = $templatingHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'extDirectApiPath',
                [$this->templatingHelper, 'getApiPath']
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
}

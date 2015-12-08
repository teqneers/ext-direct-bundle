<?php
/**
 * teqneers/ext-direct-bundle
 *
 * @category   TQ
 * @package    TQ\Bundle\ExtDirectBundle
 * @copyright  Copyright (C) 2015 by TEQneers GmbH & Co. KG
 */

namespace TQ\Bundle\ExtDirectBundle\Templating\Helper;

use TQ\Bundle\ExtDirectBundle\Helper\TemplatingHelper;

/**
 * Class ExtDirectHelper
 *
 * @package TQ\Bundle\ExtDirectBundle\Templating\Helper
 */
class ExtDirectHelper
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
    public function getName()
    {
        return 'tq_extdirect';
    }

    /**
     * @param string $endpoint
     * @param string $format
     * @return string
     */
    public function getApiPath($endpoint, $format = 'js')
    {
        return $this->templatingHelper->getApiPath($endpoint, $format);
    }
}

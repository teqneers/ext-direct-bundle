<?php
/**
 * teqneers/ext-direct-bundle
 *
 * @category   TQ
 * @package    TQ\Bundle\ExtDirectBundle
 * @copyright  Copyright (C) 2015 by TEQneers GmbH & Co. KG
 */

namespace TQ\Bundle\ExtDirectBundle;


use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use TQ\Bundle\ExtDirectBundle\DependencyInjection\Compiler\AddExtDirectServicePass;
use TQ\Bundle\ExtDirectBundle\DependencyInjection\Compiler\ValidateSecurityPass;

/**
 * Class TQExtDirectBundle
 *
 * @package TQ\Bundle\ExtDirectBundle
 */
class TQExtDirectBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddExtDirectServicePass(), PassConfig::TYPE_OPTIMIZE);
        $container->addCompilerPass(new ValidateSecurityPass());
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 22.01.16
 * Time: 12:16
 */

namespace TQ\Bundle\ExtDirectBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ValidateSecurityPass
 *
 * @package TQ\Bundle\ExtDirectBundle\DependencyInjection\Compiler
 */
class ValidateSecurityPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('tq_extdirect.router.authorization_checker')) {
            // authorization listener not enabled or expression language not available
            return;
        }

        if (!$container->hasDefinition('security.token_storage')) {
            // security bundle not available
            $container->removeDefinition('tq_extdirect.router.authorization_checker');
            $container->removeDefinition('tq_extdirect.router.listener.authorization');
            return;
        }

        if ($container->hasDefinition('sensio_framework_extra.security.expression_language.default')) {
            // sensio framework extra bundle available - us their expression language
            $container->getDefinition('tq_extdirect.router.authorization_checker')
                      ->replaceArgument(
                          0,
                          new Reference('sensio_framework_extra.security.expression_language.default')
                      );
        }
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 27.07.15
 * Time: 14:13
 */

namespace TQ\Bundle\ExtDirectBundle\Tests\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use TQ\Bundle\ExtDirectBundle\Twig\ExtDirectExtension;

/**
 * Class ExtDirectExtensionTest
 *
 * @package TQ\Bundle\ExtDirectBundle\Tests\Twig
 */
class ExtDirectExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetApiPath()
    {
        /** @var UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject $urlGenerator */
        $urlGenerator = $this->getMock(
            'Symfony\Component\Routing\Generator\UrlGeneratorInterface',
            array('generate', 'setContext', 'getContext')
        );

        $urlGenerator->expects($this->once())
                     ->method('generate')
                     ->with(
                         $this->equalTo('tq_extdirect_api'),
                         $this->equalTo(array(
                             'endpoint' => 'api',
                             '_format'  => 'js'
                         ))
                     )
                     ->willReturn('url');

        $extension = new ExtDirectExtension($urlGenerator);
        $this->assertEquals('url', $extension->getApiPath('api'));
    }

    public function testGetJsonApiPath()
    {
        /** @var UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject $urlGenerator */
        $urlGenerator = $this->getMock(
            'Symfony\Component\Routing\Generator\UrlGeneratorInterface',
            array('generate', 'setContext', 'getContext')
        );

        $urlGenerator->expects($this->once())
                     ->method('generate')
                     ->with(
                         $this->equalTo('tq_extdirect_api'),
                         $this->equalTo(array(
                             'endpoint' => 'api',
                             '_format'  => 'json'
                         ))
                     )
                     ->willReturn('url');

        $extension = new ExtDirectExtension($urlGenerator);
        $this->assertEquals('url', $extension->getApiPath('api', 'json'));
    }
}

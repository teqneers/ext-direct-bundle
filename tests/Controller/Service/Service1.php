<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 27.07.15
 * Time: 14:19
 */

namespace TQ\Bundle\ExtDirectBundle\Tests\Controller\Service;

use Symfony\Component\Validator\Constraints as Assert;
use TQ\ExtDirect\Annotation as Direct;

/**
 * Class Service1
 *
 * @package TQ\Bundle\ExtDirectBundle\Tests\Controller\Service
 *
 * @Direct\Action("app.direct.test")
 */
class Service1
{
    /**
     * @Direct\Method()
     * @Direct\Parameter("a", { @Assert\NotNull(), @Assert\Type("string") })
     *
     * @param string $a
     * @return string
     */
    public function methodA($a)
    {
        return $a;
    }
}

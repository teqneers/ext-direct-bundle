<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 27.07.15
 * Time: 14:00
 */

namespace TQ\Bundle\ExtDirectBundle\Tests\Services;

use Symfony\Component\Validator\Constraints as Assert;
use TQ\ExtDirect\Annotation as Direct;

/**
 * Class Service1
 *
 * @package TQ\Bundle\ExtDirectBundle\Tests\Services
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

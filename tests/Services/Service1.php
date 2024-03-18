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
 * @Direct\Action()
 */
#[Direct\Action()]
class Service1
{
    /**
     * @Direct\Method()
     * @Direct\Parameter("a", { @Assert\NotNull(), @Assert\Type("string") })
     */
    #[Direct\Method()]
    #[Direct\Parameter("a", [ new Assert\NotNull(), new Assert\Type("string") ])]
    public function methodA(string $a): string
    {
        return $a;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 15.01.16
 * Time: 12:22
 */

namespace TQ\Bundle\ExtDirectBundle\Router;

use TQ\ExtDirect\Router\AbstractResponse;

/**
 * Class DumpResponseDecorator
 *
 * @package TQ\Bundle\ExtDirectBundle\Router
 */
class DumpResponseDecorator extends AbstractResponse
{
    /**
     * @var AbstractResponse
     */
    private $decorated;

    /**
     * @var array
     */
    private $dumps;

    /**
     * @param AbstractResponse $decorated
     * @param array            $dumps
     */
    public function __construct(AbstractResponse $decorated, array $dumps)
    {
        parent::__construct($decorated->getType());
        $this->decorated = $decorated;
        $this->dumps     = $dumps;
    }

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        $serialized = $this->decorated->jsonSerialize();

        return array_merge(
            $serialized,
            [
                '__dump' => $this->dumps
            ]
        );
    }
}

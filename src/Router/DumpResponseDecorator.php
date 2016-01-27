<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 15.01.16
 * Time: 12:22
 */

namespace TQ\Bundle\ExtDirectBundle\Router;

use TQ\ExtDirect\Router\AbstractResponseDecorator;
use TQ\ExtDirect\Router\Response;

/**
 * Class DumpResponseDecorator
 *
 * @package TQ\Bundle\ExtDirectBundle\Router
 */
class DumpResponseDecorator extends AbstractResponseDecorator
{
    /**
     * @var array
     */
    private $dumps;

    /**
     * @param Response $decorated
     * @param array    $dumps
     */
    public function __construct(Response $decorated, array $dumps)
    {
        parent::__construct($decorated);
        $this->dumps = $dumps;
    }


    /**
     * {@inheritdoc}
     */
    protected function serializeAdditionalData()
    {
        return [
            '__dump' => $this->dumps
        ];
    }
}

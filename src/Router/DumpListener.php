<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 15.01.16
 * Time: 12:12
 */

namespace TQ\Bundle\ExtDirectBundle\Router;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\DataCollector\DumpDataCollector;
use TQ\ExtDirect\Router\Event\EndRequestEvent;
use TQ\ExtDirect\Router\ResponseCollection;
use TQ\ExtDirect\Router\RouterEvents;

/**
 * Class DumpListener
 *
 * @package TQ\Bundle\ExtDirectBundle\Router
 */
class DumpListener implements EventSubscriberInterface
{
    /**
     * @var DumpDataCollector|null
     */
    private $dumpCollector;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            RouterEvents::END_REQUEST => array('onEndRequest', -1024)
        );
    }

    /**
     * @param DumpDataCollector|null $dumpCollector
     */
    public function __construct(DumpDataCollector $dumpCollector = null)
    {
        $this->dumpCollector = $dumpCollector;
    }

    /**
     * @param EndRequestEvent $event
     */
    public function onEndRequest(EndRequestEvent $event)
    {
        if (!$this->dumpCollector) {
            return;
        }
        $collection = $event->getDirectResponse();
        if (count($collection) < 1) {
            return;
        }
        $dumps = $this->dumpCollector->getDumps('html');
        if (empty($dumps)) {
            return;
        }
        $all    = $collection->all();
        $all[0] = new DumpResponseDecorator($all[0], $dumps);

        $event->setDirectResponse(new ResponseCollection($all));
    }
}

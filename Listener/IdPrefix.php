<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Listener;

use Symfony\Cmf\Bundle\ChainRoutingBundle\Document\Route;
use Doctrine\ODM\PHPCR\Event\LifecycleEventArgs;


/**
 * Doctrine listener to set the idPrefix on new routes
 *
 * @author david.buchmann@liip.ch
 */
class IdPrefix
{
    /**
     * The prefix to add to the url to create the repository path
     *
     * @var string
     */
    protected $idPrefix = '';

    public function __construct($prefix)
    {
        $this->idPrefix = $prefix;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $this->updateId($args);
    }
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->updateId($args);
    }
    protected function updateId(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();
        if ($doc instanceof Route) {
            $doc->setPrefix($this->idPrefix);
        }
    }
}

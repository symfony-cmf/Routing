<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Document;

use Doctrine\ODM\PHPCR\DocumentRepository;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Symfony\Cmf\Bundle\ChainRoutingBundle\Routing\RouteRepositoryInterface;

/**
 * Repository to load routes from phpcr-odm
 *
 * @author david.buchmann@liip.ch
 */
class RouteRepository extends DocumentRepository implements RouteRepositoryInterface
{
    /**
     * The prefix to add to the url to create the repository path
     *
     * @var string
     */
    protected $idPrefix = '';

    public function __construct($dm, ClassMetadata $class)
    {
        parent::__construct($dm, $class);
        // let dm determine class so this repository works for all types of routes
        $this->className = null;
    }

    public function setPrefix($prefix)
    {
        $this->idPrefix = $prefix;
    }

    /**
     * {@inheritDoc}
     */
    public function findByUrl($url)
    {
        try {
            return $this->find($this->idPrefix . $url);
        } catch (\PHPCR\RepositoryException $e) {
            // TODO: how to determine whether this is a relevant exception or not?
            // for example, getting /my//test (note the double /) is just an invalid path
            // and means another router might handle this.
            // but if the phpcr backend is down for example, we want to alert the user
            return null;
       }
    }
}
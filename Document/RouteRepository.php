<?php

namespace Symfony\Cmf\Bundle\ChainRoutingBundle\Document;

use Doctrine\ODM\PHPCR\DocumentRepository;
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


    public function setPrefix($prefix)
    {
        $this->idPrefix = $prefix;
    }

    public function findByUrl($url)
    {
        return $this->find($this->idPrefix . $url);
    }

    /**
     * Overwritten to set the prefix to allow the document to calculate its url
     */
    public function createDocument($node, array &$hints = array()) {
        $doc = parent::createDocument($node, $hints);
        $doc->setPrefix($this->idPrefix);
        return $doc;
    }

}
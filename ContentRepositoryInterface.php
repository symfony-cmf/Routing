<?php

namespace Symfony\Cmf\Component\Routing;

/**
 * Interface used by the DoctrineRouter to retrieve content by it's id.
 *
 * This can be easily implemented using the DocumentManager.
 *
 * @author Uwe Jäger
 */
interface ContentRepositoryInterface
{
    /**
     * Return a content object by it's id or null if there is none.
     *
     * @abstract
     * @param $id id of the content object
     * @return mixed
     */
    function findById($id);
}
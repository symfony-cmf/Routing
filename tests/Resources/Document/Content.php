<?php

namespace Symfony\Cmf\Component\Routing\Tests\Resources\Document;

/**
 * @PHPCRODM\Document(referenceable=true)
 */
class Content
{
    /**
     * @PHPCRODM\Id(strategy="parent")
     */
    public $id;

    /**
     * @PHPCRODM\ParentDocument
     */
    public $parent;

    /**
     * @PHPCRODM\NodeName
     */
    public $name;

    /**
     * @PHPCRODM\String
     */
    public $title;
}

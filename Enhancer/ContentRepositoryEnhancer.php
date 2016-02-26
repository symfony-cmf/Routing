<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Enhancer;

use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;

class ContentRepositoryEnhancer implements RouteEnhancerInterface
{
    /**
     * @var ContentRepositoryInterface
     */
    private $contentRepository;

    /**
     * @var string
     */
    private $target;

    /**
     * @var string
     */
    private $source;

    /**
     * ContentRepositoryEnhancer constructor.
     *
     * @param ContentRepositoryInterface $contentRepository
     * @param string                     $target
     * @param string                     $source
     */
    public function __construct(
        ContentRepositoryInterface $contentRepository,
        $target = RouteObjectInterface::CONTENT_OBJECT,
        $source = RouteObjectInterface::CONTENT_ID)
    {
        $this->contentRepository = $contentRepository;
        $this->target = $target;
        $this->source = $source;
    }

    /**
     *{@inheritdoc}
     */
    public function enhance(array $defaults, Request $request)
    {
        if (!isset($defaults[$this->target]) && isset($defaults[$this->source])) {
            $defaults[$this->target] = $this->contentRepository->findById($defaults[$this->source]);
        }

        return $defaults;
    }
}

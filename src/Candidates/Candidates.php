<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Candidates;

use Symfony\Component\HttpFoundation\Request;

/**
 * A straightforward strategy that splits the URL on "/".
 *
 * If locales is set, additionally generates candidates removing the locale if
 * it is one of the configured locales, for non-locale specific URLs.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class Candidates implements CandidatesInterface
{
    /**
     * @var string[]
     */
    protected array $locales;

    /**
     * A limit to apply to the number of candidates generated.
     *
     * This is to prevent abusive requests with a lot of "/". The limit is per
     * batch, that is if a locale matches you could get as many as 2 * $limit
     * candidates if the URL has that many slashes.
     */
    protected int $limit;

    /**
     * @param string[] $locales The locales to support
     * @param int      $limit   A limit to apply to the candidates generated
     */
    public function __construct(array $locales = [], int $limit = 20)
    {
        $this->setLocales($locales);
        $this->limit = $limit;
    }

    /**
     * Set the locales to support by this strategy.
     *
     * @param string[] $locales The locales to support
     */
    public function setLocales(array $locales): void
    {
        $this->locales = $locales;
    }

    /**
     * {@inheritdoc}
     *
     * Always returns true.
     */
    public function isCandidate(string $name): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * Does nothing.
     */
    public function restrictQuery(object $queryBuilder): void
    {
    }

    public function getCandidates(Request $request): array
    {
        $url = $request->getPathInfo();
        $candidates = $this->getCandidatesFor($url);

        $locale = $this->determineLocale($url);
        if ($locale) {
            $candidates = array_unique(array_merge($candidates, $this->getCandidatesFor(substr($url, strlen($locale) + 1))));
        }

        return $candidates;
    }

    /**
     * Determine the locale of this URL.
     *
     * @return string|bool The locale if $url starts with one of the allowed locales
     */
    protected function determineLocale(string $url): string|bool
    {
        if (!count($this->locales)) {
            return false;
        }

        $matches = [];
        if (preg_match('#^/('.implode('|', $this->locales).')(/|$)#', $url, $matches)) {
            return $matches[1];
        }

        return false;
    }

    /**
     * Handle a possible format extension and split the $url on "/".
     *
     * $prefix is prepended to every candidate generated.
     *
     * @return string[] Paths that could represent routes that match $url and are
     *                  child of $prefix
     */
    protected function getCandidatesFor(string $url, string $prefix = ''): array
    {
        $candidates = [];
        if ('/' !== $url) {
            // handle format extension, like .html or .json
            if (preg_match('/(.+)\.[a-z]+$/i', $url, $matches)) {
                $candidates[] = $prefix.$url;
                $url = $matches[1];
            }

            $part = $url;
            $count = 0;
            while (false !== ($pos = strrpos($part, '/'))) {
                if (++$count > $this->limit) {
                    return $candidates;
                }
                $candidates[] = $prefix.$part;
                $part = substr($url, 0, $pos);
            }
        }

        $candidates[] = $prefix ?: '/';

        return $candidates;
    }
}

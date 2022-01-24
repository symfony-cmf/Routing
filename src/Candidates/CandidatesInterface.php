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
 * Candidates is a subsystem useful for the route provider. It separates the
 * logic for determining possible static prefixes from the route provider.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
interface CandidatesInterface
{
    /**
     * @return string[] a list of paths
     */
    public function getCandidates(Request $request): array;

    /**
     * Determine if $name is a valid candidate, e.g. in getRouteByName.
     */
    public function isCandidate(string $name): bool;

    /**
     * Provide a best effort query restriction to limit a query to only find
     * routes that are supported.
     *
     * @param object $queryBuilder A query builder suited for the storage backend
     */
    public function restrictQuery(object $queryBuilder): void;
}

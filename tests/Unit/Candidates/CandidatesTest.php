<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\Routing\Tests\Unit\Candidates;

use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Component\Routing\Candidates\Candidates;
use Symfony\Component\HttpFoundation\Request;

class CandidatesTest extends TestCase
{
    /**
     * Everything is a candidate.
     */
    public function testIsCandidate()
    {
        $candidates = new Candidates();
        $this->assertTrue($candidates->isCandidate('/routes'));
        $this->assertTrue($candidates->isCandidate('/routes/my/path'));
    }

    /**
     * Nothing should be called on the query builder.
     */
    public function testRestrictQuery()
    {
        $candidates = new Candidates();
        $candidates->restrictQuery(null);
    }

    public function testGetCandidates()
    {
        $request = Request::create('/my/path.html');

        $candidates = new Candidates();
        $paths = $candidates->getCandidates($request);

        $this->assertEquals(
            [
                '/my/path.html',
                '/my/path',
                '/my',
                '/',
            ],
            $paths
        );
    }

    public function testGetCandidatesLocales()
    {
        $candidates = new Candidates(['de', 'fr']);

        $request = Request::create('/fr/path.html');
        $paths = $candidates->getCandidates($request);

        $this->assertEquals(
            [
                '/fr/path.html',
                '/fr/path',
                '/fr',
                '/',
                '/path.html',
                '/path',
            ],
            $paths
        );

        $request = Request::create('/it/path.html');
        $paths = $candidates->getCandidates($request);

        $this->assertEquals(
            [
                '/it/path.html',
                '/it/path',
                '/it',
                '/',
            ],
            $paths
        );
    }

    public function testGetCandidatesLimit()
    {
        $candidates = new Candidates([], 1);

        $request = Request::create('/my/path/is/deep.html');

        $paths = $candidates->getCandidates($request);

        $this->assertEquals(
            [
                '/my/path/is/deep.html',
                '/my/path/is/deep',
            ],
            $paths
        );
    }
}

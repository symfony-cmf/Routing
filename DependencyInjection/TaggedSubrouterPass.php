<?php
/*
Copyright (C) 2011 by E-butik i Norden AB

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

namespace Symfony\Bundle\ChainRoutingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class TaggedSubrouterPass implements CompilerPassInterface
{
    /**
     * Adds any tagged subrouters to the chain router, as well as router.real if it exists.
     * Router.real is added at priority 100.
     *
     * @see Symfony\Component\DependencyInjection\Compiler.CompilerPassInterface::process()
     * @author Magnus Nordlander <magnus@e-butik.se>
     *
     * @param Symfony\Component\DependencyInjection\ContainerBuilder The container builder
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('chain_router')) {
            return;
        }

        $taggedServiceHolder = $container->getDefinition('chain_router');

        if ($container->hasDefinition('router.real')) {
            $taggedServiceHolder->addMethodCall('addSubRouter', array(new Reference('router.real'), 100));
        }

        foreach ($container->findTaggedServiceIds('chain_router.subrouters') as $id => $attributes) {
            $priority = $attributes[0]['priority'];
            $taggedServiceHolder->addMethodCall('addSubRouter', array(new Reference($id), $priority));
        }
    }
}


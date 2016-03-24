<?php

namespace AppBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('prayno.cas_authenticator')) {
            return;
        }
        $definition = $container->getDefinition('prayno.cas_authenticator');
        $definition->setClass('AppBundle\\Security\\RecolnatAuthenticator');
        $definition->setArguments($definition->getArguments());
    }
}

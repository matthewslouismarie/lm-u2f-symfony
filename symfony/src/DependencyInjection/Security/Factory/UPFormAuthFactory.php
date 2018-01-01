<?php

namespace App\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use App\Security\Authentication\Provider\UPFormAuthProvider;
use App\Security\Firewall\UPFormAuthListener;

class UPFormAuthFactory implements SecurityFactoryInterface
{
    public function create(
        ContainerBuilder $container,
        $id,
        $config,
        $userProvider,
        $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.up_form_auth.'.$id;
        $container
            ->setDefinition($providerId, new ChildDefinition(UPFormAuthProvider::class))
            ->setArgument(0, new Reference($userProvider))
        ;

        $listenerId = 'security.authentication.listener.up_form_auth.'.$id;
        $listener = $container->setDefinition($listenerId, new ChildDefinition(UPFormAuthListener::class));

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'form';
    }

    public function getKey()
    {
        return 'up_form_auth';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
}
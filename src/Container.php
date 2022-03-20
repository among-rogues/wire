<?php declare(strict_types=1);
/**
 * This file is part of the Rogue Component Multiverse
 * 
 * (c) 2022 Matthias 'nihylum' Kaschubowski
 * 
 * @package rogue.wire
 */
namespace Rogue\Wire;

use SplObjectStorage;
use Rogue\Wire\Exception\IncompatibilityException;
use Rogue\Wire\Exception\InterfaceNotFoundException;
use Rogue\Wire\Exception\BlockedInterfaceException;

/**
 * Dependency Container
 * 
 * @author Matthias Kaschubowski <nihylum@gmail.com>
 * @api 1.0
 */
class Container implements ContainerInterface {

    /**
     * holds the container strategy
     * 
     * @var ContainerStrategyInterface
     */
    protected ContainerStrategyInterface $strategy;

    /**
     * holds all connected containers
     * 
     * @var SplObjectStorage
     */
    protected SplObjectStorage $containerPool;

    /**
     * holds all interface definitions
     * 
     * @var Definition[]
     */
    protected array $definitions = [];

    /**
     * holds all ignored interfaces by this container which must be delegated to the connected
     * 
     * @var string[]
     */
    protected array $ignoredInterfaces = [];

    /**
     * holds all service provider class names who had been already registered to this container
     * 
     * @var string[]
     */
    protected array $serviceProviders = [];

    /**
     * defines if the container is in share mode for new services, if so services will be automatically set shared
     * 
     * @var bool
     */
    protected bool $defaultsToShare = false;

    /**
     * The Constructor
     * 
     * @param null|ContainerStrategyInterface $strategy
     */
    public function __construct(ContainerStrategyInterface $strategy = null)
    {
        $this->containerPool = new SplObjectStorage();
        $this->strategy = $strategy ?? new ManualWiringStrategy();
    }

    /**
     * @inherit
     * 
     * @throws InterfaceNotFoundException if the interface could not be serviced to an object
     */
    public function get(string $interface): object
    {
        if ( in_array($interface, $this->ignoredInterfaces) && $this->containerPool->count() === 0 ) {
            throw new InterfaceNotFoundException(
                'Unable to resolve blocked interface `'.$interface.'`'
            );
        }

        if ( in_array($interface, $this->ignoredInterfaces) && $this->containerPool->count() > 0 ) {
            foreach ( $this->containerPool as $container ) {
                if ( $container->has($interface) ) {
                    return $container->get($interface);
                }

                if ( ! $container->has($interface) && $container->doesIgnore($interface) && $container->hasConnections() ) {
                    return $container->get($interface);
                }
            }

            throw new InterfaceNotFoundException(
                'Unable to resolve interface `'.$interface.'`'
            );
        }

        $definition = $this->definitions[$interface];

        if ( $definition->isShared() && $definition->hasInstance() ) {
            return $definition->getInstance();
        }

        return $this->strategy->build($definition, $this);
    }

    /**
     * @inherit
     */
    public function has(string $interface): bool
    {
        if ( in_array($interface, $this->blockedInterfaces) ) {
            return false;
        }

        if ( array_key_exists($interface, $this->definitions) ) {
            return true;
        }

        foreach ( $this->containerPool as $container ) {
            if ( $container->has($interface) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inherit
     */
    public function add(string $interface, $concrete = null): ConcreteInterface
    {
        $concrete = $concrete ?? $interface;
        
        $this->definitions[$interface] = $definition = $this->strategy->canAggregateEntities() && is_string($concrete)
            ? $this->createFromAttributes($interface, $concrete) 
            : new Definition($interface, $concrete)
        ;

        return new Concrete($definition);
    }

    /**
     * @inherit
     * 
     * @throws IncompatibilityException when the array is not well formed
     * @throws BlockedInterfaceException when a interface is blocked
     */
    public function wire(array $interfaces): ContainerInterface
    {
        foreach ( $interfaces as $key => $value ) {
            if ( is_int($key) && in_array($value, $this->ignoredInterfaces) ) {
                throw new BlockedInterfaceException(
                    'Can not add `'.$value.'`, interface is blocked by this container'
                );
            }

            if ( is_int($key) ) {
                $this->add($value);
                continue;
            }

            if ( is_string($key) && in_array($key, $this->ignoredInterfaces) ) {
                throw new BlockedInterfaceException(
                    'Can not add `'.$key.'`, interface is blocked by this container'
                );
            }

            if ( ! is_string($value) ) {
                throw new IncompatibilityException(
                    'when using key => value, the value must be a concrete class name, '.gettype($value).' given.'
                );
            }
            
            $this->add($key, $value);
        }

        return $this;
    }

    /**
     * @inherit
     * 
     * @throws BlockedInterfaceException when the interface is blocked
     */
    public function singleton(string $interface, $concrete = null): ConcreteInterface
    {
        if ( in_array($interface, $this->ignoredInterfaces) ) {
            throw new BlockedInterfaceException(
                'Can not add singleton `'.$interface.'`, interface is blocked by this container'
            );
        }

        return $this->add($interface, $concrete)->shared(true);
    }

    /**
     * @inherit
     * 
     * @throws BlockedInterfaceException when the interface is blocked
     */
    public function extend(string $interface, callable $callback): ContainerInterface
    {
        if ( in_array($interface, $this->ignoredInterfaces) ) {
            throw new BlockedInterfaceException(
                'Can not add extension to `'.$interface.'`, interface is blocked by this container'
            );
        }

        $this->concrete($interface)->extend($callback);

        return $this;
    }

    /**
     * @inherit
     */
    public function extendIf(string $interface, callable $callback): ContainerInterface
    {
        if ( array_key_exists($interface, $this->definitions) && ( ! in_array($interface, $this->blockedInterfaces) ) ) {
            return $this->extend($interface, $callback);
        }

        return $this;
    }

    /**
     * @inherit
     * 
     * @throws BlockedInterfaceException when the interface is blocked
     * @throws InterfaceNotFoundException when the interface is unkown to this container
     */
    public function concrete(string $interface): ConcreteInterface
    {
        if ( in_array($interface, $this->ignoredInterfaces) ) {
            throw new BlockedInterfaceException(
                'Can not fetch concrete instance for interface `'.$interface.'`, interface is blocked by this container'
            );
        }

        if ( ! array_key_exists($interface, $this->definitions) ) {
            throw new InterfaceNotFoundException(
                'Can not fetch concrete instance for interface `'.$interface.'`, interface is not known to this container'
            );
        }

        return new Concrete($this->definitions[$interface]);
    }

    /**
     * @inherit
     */
    public function connectWith(ContainerInterface ... $containers): ContainerInterface
    {
        foreach ( $containers as $container ) {
            if ( ! $this->containerPool->contains($container) ) {
                $this->containerPool->attach($container);
            }
        }

        return $this;
    }

    /**
     * @inherit
     */
    public function disconnectFrom(ContainerInterface ... $containers): ContainerInterface
    {
        foreach ( $containers as $container ) {
            if ( $this->containerPool->contains($container) ) {
                $this->containerPool->detach($container);
            }
        }
    }

    /**
     * @inherit
     */
    public function ignore(string ... $interfaces): ContainerInterface
    {
        $newInterfaces = array_diff($interfaces, $this->ignoredInterfaces);
        $this->ignoredInterfaces = array_merge($this->ignoredInterfaces, $newInterfaces);

        return $this;
    }

    /**
     * @inherit
     */
    public function unignore(string ... $interfaces): ContainerInterface
    {
        if ( empty($interfaces) ) {
            $this->ignoredInterfaces = [];
            
            return $this;
        }

        $this->ignoredInterfaces = array_diff($this->ignoredInterfaces, $interfaces);

        return $this;
    }

    /**
     * @inherit
     */
    public function doesIgnore(string ... $interfaces): bool
    {
        foreach ( $interfaces as $interface ) {
            if ( ! in_array($interface, $this->ignoredInterfaces) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inherit
     */
    public function share(string ... $interfaces): ContainerInterface
    {
        foreach ( $interfaces as $interface ) {
            if ( $this->has($interface) ) {
                $this->definitions[$interface]->setShared(true);
            }
        }

        return $this;
    }

    /**
     * @inherit
     */
    public function hasConnections(): bool
    {
        return $this->containerPool->count() > 0;
    }

    /**
     * @inherit
     */
    public function unshare(string ... $interfaces): ContainerInterface
    {
        foreach ( $interfaces as $interface ) {
            if ( $this->has($interface) ) {
                $this->definitions[$interface]->setShared(false);
                $this->definitions[$interface]->clearInstance();
            }
        }

        return $this;
    }

    /**
     * @inherit
     */
    public function doesShare(string ... $interfaces): bool
    {
        foreach ( $interfaces as $interface ) {
            if ( ! $this->has($interface) ) {
                return false;
            }

            if ( ! $this->definitions[$interface]->isShared() ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inherit
     */
    public function defaultToShare(bool $value): ContainerInterface
    {
        $this->defaultsToShare = $value;

        return $this;
    }

    public function register(ServiceProviderInterface | string ... $providers): ContainerInterface
    {
        foreach ( $providers as $provider ) {
            $item = is_object($provider) ? get_class($provider) : $provider;

            if ( in_array($item, $this->serviceProviders) ) {
                continue;
            }

            $serviceProvider = is_object($provider) ? $provider : $this->make($provider);

            $serviceProvider->register($this);

            $this->serviceProviders[] = $item;
        }

        // TODO - maybe elevating exceptions

        return $this;
    }

    public function supports(ServiceProviderInterface | string  $provider, callable $context = null): bool|ContainerInterface
    {
        $item = is_object($provider) ? get_class($provider) : $provider;

        return in_array($item, $this->serviceProviders);
    }

    public function make(string $interface): object
    {
        $definition = array_key_exists($interface, $this->definitions) 
            ? $this->definitions[$interface] 
            : new Definition($interface, $interface)
        ;

        // TODO - maybe elevating exceptions

        return $this->strategy->build($definition, $this, true);
    }

}

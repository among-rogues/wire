<?php declare(strict_types=1);
/**
 * This file is part of the Rogue Component Multiverse
 * 
 * (c) 2022 Matthias 'nihylum' Kaschubowski
 * 
 * @package rogue.wire
 */
namespace Rogue\Wire;

use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Container Interface
 * 
 * @author Matthias Kaschubowski <nihylum@gmail.com>
 * @api 1.0
 */
interface ContainerInterface extends PsrContainerInterface {

    /**
     * @inherit
     * 
     * @param string $interface instead of $id from the PSR Implementation
     * @return object instead of mixed from the PSR Implementation
     */
    public function get(string $interface): object;

    /**
     * @inherit
     * 
     * @param string $interface instea of $id from the PSR Implementation
     */
    public function has(string $interface): bool;

    public function add(string $interface, $concrete = null): ConcreteInterface;

    /**
     * Proxy method for add() but will only accept class names. The provided array
     * may deliver the interface name as a key associated to its concrete class or
     * as a value hold by an integer key.
     * 
     * @param string[] $interfaces
     * @return ContainerInterface
     */
    public function wire(array $interfaces): ContainerInterface;

    /**
     * proxy method for add() to automatically share the interface.
     * 
     * @param string $interface
     * @param mixed $concrete
     * @return ConcreteInterface
     */
    public function singleton(string $interface, $concrete = null): ConcreteInterface;

    /**
     * proxy method to concrete() to automatically add the callback as an extension to the interface.
     * 
     * @param string $interface
     * @param callable $callback
     * @return ConcreteInterface
     */
    public function extend(string $interface, callable $callback): ContainerInterface;

    /**
     * proxy method to extend() but skips the extension when the interface is blocked or not known.
     * 
     * @param string $interface
     * @param callable $callback
     * @return ContainerInterface
     */
    public function extendIf(string $interface, callable $callback): ContainerInterface;

    /**
     * returns the concrete interface for an already known interface.
     * 
     * @param string $interface
     * @return ConcreteInterface
     */
    public function concrete(string $interface): ConcreteInterface;

    /**
     * Ignores the provided interfaces.
     * 
     * @param string[] $interfaces
     * @return ContainerInterface
     */
    public function ignore(string ... $interfaces): ContainerInterface;

    /**
     * Unignores the provided Interfaces.
     * 
     * @param string[] $interfaces
     * @return ContainerInterface
     */
    public function unignore(string ... $interfaces): ContainerInterface;

    /**
     * Checks whether the provided interfaces are all ignored or not.
     * 
     * @param string[] $interfaces
     * @return bool
     */
    public function doesIgnore(string ... $interfaces): bool;

    /**
     * Marks the provided interfaces as being singletons.
     * 
     * @param string[] $interfaces
     * @return ContainerInterface
     */
    public function share(string ... $interfaces): ContainerInterface;

    /**
     * Marks the provided interfaces as being multitons.
     * 
     * @param string[] $interfaces
     * @return ContainerInterface
     */
    public function unshare(string ... $interfaces): ContainerInterface;

    /**
     * Checks whether all provided interfaces are marked as singletons or not.
     * 
     * @param string[] $interfaces
     * @return bool
     */
    public function doesShare(string ... $interfaces): bool;

    /**
     * Enables/Disables the container to mark all registered services after calling this method
     * as being singletons (enable) or multitons (disable).
     * 
     * @param bool $value
     * @return ContainerInterface
     */
    public function defaultToShare(bool $value): ContainerInterface;

    /**
     * Connects this container to the provided container instances, if not already connected.
     * 
     * @param ContainerInterface[] $containers
     * @return ContainerInterface
     */
    public function connectWith(ContainerInterface ... $containers): ContainerInterface;

    /**
     * Disconnects this container from the provided container instances, if not already disconnected.
     * 
     * @param ContainerInterface[] $containers
     * @return ContainerInterface
     */
    public function disconnectFrom(ContainerInterface ... $containers): ContainerInterface;

    /**
     * Checks whether the container is connected to any other container or not.
     * 
     * @return bool
     */
    public function hasConnections(): bool;

    /**
     * registers a service provided by calling its register method in scope of this container.
     * 
     * @param ServiceProviderInterface[]|string[] $providers
     */
    public function register(ServiceProviderInterface | string ... $providers): ContainerInterface;

    /**
     * checks whether the provided service provider instance or classname is supported by (registered to)
     * this container. If so an optional context callback is executed and the container instance is return
     * or if no context callback is provided an boolean is return, delivering true if so, or false when not.
     * 
     * @param ServiceProviderInterface|string $provider
     * @return bool|ContainerInterface
     */
    public function supports(ServiceProviderInterface | string  $provider, callable $context = null): bool|ContainerInterface;

    /**
     * creates the given interface close to independently from the container. Will use the stored definition when the interface
     * is known to the container but will ignore the share mechanism. Will create a own definition when the interface is not
     * known to the container and after the the creation, will destroy the created one-time definition.
     * 
     * @param string $interface
     * @return object
     */
    public function make(string $interface): object;
}

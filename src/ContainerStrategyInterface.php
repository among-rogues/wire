<?php declare(strict_types=1);
/**
 * This file is part of the Rogue Component Multiverse
 * 
 * (c) 2022 Matthias 'nihylum' Kaschubowski
 * 
 * @package rogue.wire
 */
namespace Rogue\Wire;

/**
 * Container Strategy Interface
 * 
 * @author Matthias Kaschubowski <nihylum@gmail.com>
 * @api 1.0
 */
interface ContainerStrategyInterface {

    /**
     * checks whether the strategy does support coordinated entities or not.
     * 
     * @return bool
     */
    public function supportsCoordinatedEntities(): bool;

    /**
     * checks whether the strategy does support automatically resolving of entities or not.
     * 
     * @return bool
     */
    public function canAutomaticallyResolveEntities(): bool;

    /**
     * checks whether the strategy does support aggregation of entities (extracting wire instructions from attributes) or not.
     * 
     * @return bool
     */
    public function canAggregateEntities(): bool;

    /**
     * assembles the Definition-object from the targeted concrete.
     */
    public function createFromAttributes(string $interface, string $concrete): Definition;

    /**
     * enjures that the given callback is valid towards the provided serviced interface and concrete class.
     * 
     * @param callable $callback
     * @param string $servicedInterface
     * @param string $concrete
     * @return callback
     */
    public function sanitizeCallback(callable $callback, string $servicedInterface, string $concrete): callback;

    /**
     * Builds the provided definition
     * 
     * @param Definition $definition
     * @param ContainerInterface $containerScope
     * @param bool $ignoreSharing
     * @return object
     */
    public function build(Definition $definition, ContainerInterface $containerScope, bool $ignoreSharing = false): object;
    
}

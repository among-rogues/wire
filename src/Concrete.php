<?php declare(strict_types=1);
/**
 * This file is part of the Rogue Component Multiverse
 * 
 * (c) 2022 Matthias 'nihylum' Kaschubowski
 * 
 * @package rogue.wire
 */
namespace Rogue\Wire;

use Rogue\Wire\Exception\IncompatibilityException;
use Rogue\Wire\ValueInterface;
use Generator;

/**
 * Concrete Implementor Class
 * 
 * @author Matthias Kaschubowski <nihylum@gmail.com>
 * @api 1.0
 */
class Concrete implements ConcreteInterface {

    /**
     * holds the definition for this concrete service
     * 
     * @var Definition
     */
    protected Definition $definition;

    /**
     * holds the container strategy instance
     * 
     * @var ContainerStrategyInterface
     */
    protected ContainerStrategyInterface $containerStrategy;

    /**
     * The Constructor
     * 
     * @param string $interface
     * @param mixed $concrete
     */
    public function __construct(ContainerStrategyInterface $strategy, string $interface, $concrete = null)
    {
        $this->containerStrategy = $strategy;
        $this->definition = new Definition($interface);

        if ( is_callable($concrete) ) {
            $this->definition->setFactory($concrete);

            return;
        }

        if ( is_string($concrete) ) {
            $this->definition->setConcreteClass($concrete);

            return;
        }

        if ( is_object($concrete) ) {
            $this->definition->setInstance($concrete);

            return;
        }
        
        throw new IncompatibilityException(
            'unsupported type `'.gettype($concrete).'` for concrete parameter, must be callable, string or object'
        );
    }

    /**
     * @inherit
     * 
     * @throws IncompatibilityException when the parameter format is not supported
     */
    public function withMethodCall(string $method, ? array $parameters = null): ConcreteInterface
    {
        $namedParameters = array_filter($parameters ?? [], 'is_string');

        if ( false === $this->strategy->supportsCoordinatedEntities() && count($namedParameters) > 0 ) {
            throw new IncompatibilityException(
                'You can not use named parameters at this container, name parameters are not supported'
            );
        }

        $parameterKeys = array_keys($parameters ?? []);

        if ( false === $this->strategy->supportsCoordinatedEntities() && $parameterKeys !== range(0, count($parameterKeys) - 1) ) {
            throw new IncompatibilityException(
                'You can not use coordinated numeric indexes at this container, coordinatec numeric parameters are not supported'
            );
        }

        $this->definition->setMethodCall($method, $parameters);

        return $this;
    }

    /**
     * @inherit
     * 
     * @throws IncompatibilityException when the parameter format is not supported or the method name is invalid
     */
    public function withMethodCalls(array $methodsWithParametersAssigned): ConcreteInterface
    {
        foreach ( $methodsWithParametersAssigned as $method => $parameters ) {
            if ( ! is_string($method) ) {
                throw new IncompatibilityException(
                    'method names shall never be numeric'
                );
            }

            $this->withMethodCall($method, $parameters);
        }

        return $this;
    }

    /**
     * @inherit
     * 
     * @throws IncompatibilityException when the concrete behavior is not supported
     */
    public function withParameter(string $parameter, $concrete = null): ConcreteInterface
    {
        if ( func_num_args() === 1 && ( ! $this->strategy->canAutomaticallyResolveEntities() ) ) {
            throw new IncompatibilityException(
                'You can not omit the concrete definition for a parameter at this container, automated resolving is not supported'
            );
        }

        $this->definition->setParameter($parameter, $concrete);

        return $this;
    }

    /**
     * @inherit
     * 
     * @throws IncompatibilityException when the concrete behavior is not supported
     */
    public function withParameters(array $parametersWithConcretesAssigned): ConcreteInterface
    {
        foreach ( $parametersWithConcretesAssigned as $parameter => $concrete ) {
            $this->withParameter($parameter, $concrete);
        }

        return $this;
    }

    /**
     * @inherit
     * 
     * @throws IncompatibilityException when the concrete behavior is not supported
     */
    public function withProperty(string $property, $concrete = null): ConcreteInterface
    {
        if ( func_num_args() === 1 && ( ! $this->strategy->canAutomaticallyResolveEntities() ) ) {
            throw new IncompatibilityException(
                'You can not omit the concrete definition for a parameter at this container, automated resolving is not supported'
            );
        }

        $this->definition->setProperty($property, $concrete);

        return $this;
    }

    /**
     * @inherit
     * 
     * @throws IncompatibilityException when the concrete behavior is not supported
     */
    public function withProperties(array $propertiesWithConcretesAssigned): ConcreteInterface
    {
        foreach ( $propertiesWithConcretesAssigned as $property => $concrete ) {
            $this->withProperty($property, $concrete);
        }

        return $this;
    }

    /**
     * @inherit
     * 
     * @throws IncompatibilityException when the concrete class does not support the serviced interface
     */
    public function withConcreteClass(string $concrete): ConcreteInterface
    {
        if ( ! is_a($concrete, $this->definition->getInterface(), true) ) {
            throw IncompatibilityException(
                'Provided concrete class does not support serviced interface'
            );
        }

        $this->definition->setConcreteClass($concrete);

        return $this;
    }

    /**
     * @inherit
     * 
     * @throws IncompatibiliyException if the callback may not match the serviced interface and concrete
     */
    public function withFactory(callable $callback): ConcreteInterface
    { 
        try {
            $this->definition->setFactory(
                $this->strategy->sanitizeCallback(
                    $callback, 
                    $this->definition->getInterface(), 
                    $this->definition->getConcreteClass()
                )
            );
        }
        catch ( IncompatibilityException $exception ) {
            throw $exception;
        }

        return $this;
    }

    /**
     * @inherit
     * 
     * @throws IncompatibiliyException if the callback may not match the serviced interface and concrete
     */
    public function extend(callable $callback): ConcreteInterface
    {
        try {
            $this->definition->addExtension(
                $this->strategy->sanitizeCallback(
                    $callback,
                    $this->definition->getInterface(),
                    $this->definition->getConcreteClass()
                )
            );
        }
        catch ( IncompatibilityException $exception ) {
            throw $exception;
        }

        return $this;
    }

    /**
     * @inherit
     */
    public function forgetInstance(): ConcreteInterface
    {
        $this->definition->clearInstance();

        return $this;
    }

    /**
     * @inherit
     */
    public function shared(bool $switch = true): ConcreteInterface
    {
        if ( false === $switch ) {
            $this->definition->clearInstance();
        }

        $this->definition->setShared($switch);

        return $this;
    }
    
}

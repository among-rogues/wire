<?php declare(strict_types=1);
/**
 * This file is part of the Rogue Component Multiverse
 * 
 * (c) 2022 Matthias 'nihylum' Kaschubowski
 * 
 * @package rogue.wire
 */
namespace Rogue\Wire;

use Generator;

/**
 * Concrete Implementor Interface
 * 
 * @author Matthias Kaschubowski <nihylum@gmail.com>
 * @api 1.0
 */
interface ConcreteInterface {

    /**
     * sets method calls for the provided method. Parameters are bound to the contract to be delegated
     * into ValueInterface objects, string parameters are considered as classes that must be resolved.
     * If a parameter must be string use the result of Rogue\Wire\Value\GenericValue::create($value) 
     * as an item of the parameters-array parameter of this method.
     * 
     * @param string $method
     * @param array $parameters
     * @return ConcreteInterface
     */
    public function withMethodCall(string $method, ? array $parameters): ConcreteInterface;

    /**
     * proxy method for withMethodCall to set multiple methods at once using an array
     * 
     * @param array[] $methodsWithParamtersAssigned
     * @return ConcreteInterface
     */
    public function withMethodCalls(array $methodsWithParametersAssigned): ConcreteInterface;

    /**
     * sets the paramter for the provided parameter name. The concrete Parameter is bound to the contract
     * to be delegated into ValueInterface Objects, string concretes are considered as a class that must be
     * resolved. If a concrete must be a string use the result of Rogue\Wire\Value\GenericValue::create($value)
     * as the concrete parameter to this method.
     * 
     * @param string $parameter
     * @param null|mixed $concrete
     * @return ConcreteInterface
     */
    public function withParameter(string $parameter, $concrete = null): ConcreteInterface;

    /**
     * proxy method for withParameter to set multiple parameters at once using an array
     * 
     * @param array[] $parametersWithConcretesAssigned
     * @return ConcreteInterface
     */
    public function withParameters(array $parametersWithConcretesAssigned): ConcreteInterface;

    /**
     * sets the property for the provided property name. The concrete Parameter is boun to the contract to be
     * delegate into ValueInterface Objects, string concretes are considered as a class that must be resolved.
     * If a concrete must be a string use the result of Rogue\Wire\Value\GenericValue::create($value) as the
     * concrete parameter to this method.
     * 
     * @param string $property
     * @param null|mixed $concrete
     * @return ConcreteInterface
     */
    public function withProperty(string $property, $concrete = null): ConcreteInterface;

    /**
     * proxy method for withProperty to set mutliple properties at once using an array
     * 
     * @param array[] $propertiesWithConcretesAssigned
     * @return ConcreteInterface
     */
    public function withProperties(array $propertiesWithConcretesAssigned): ConcreteInterface;

    /**
     * sets the concrete class.
     * 
     * @param string $concrete
     * @return ConcreteInterface
     */
    public function withConcreteClass(string $concrete): ConcreteInterface;

    /**
     * set the factory.
     * 
     * @param callable $callback
     * @return ConcreteInterface
     */
    public function withFactory(callable $callback): ConcreteInterface;

    /**
     * adds an extending callback.
     * 
     * @param callable $callback
     * @return ConcreteInterface
     */
    public function extend(callable $callback): ConcreteInterface;

    /**
     * drops the instance, if any.
     * 
     * @return ConcreteInterface
     */
    public function forgetInstance(): ConcreteInterface;

    /**
     * set the shared state.
     * 
     * @param bool|true $switch
     * @return ConcreteInterface
     */
    public function shared(bool $switch = true): ConcreteInterface;
    
}

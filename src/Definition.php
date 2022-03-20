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
 * General Definition Class
 * 
 * @author Matthias Kaschubowski <nihylum@gmail.com>
 * @api 1.0
 */
class Definition {

    /**
     * holds the interface name
     * 
     * @var string
     */
    protected string $interface;

    /**
     * holds the concrete class name
     * 
     * @var string
     */
    protected string $concreteClass;

    /**
     * holds the factory, if any
     * 
     * @var null|callable
     */
    protected $factory = null;

    /**
     * holds all extensions for the defined service
     * 
     * @var callable[]
     */
    protected array $extensions = [];

    /**
     * holds all parameters to be set at the constructor
     * 
     * @var ValueInterface[]
     */
    protected array $parameters = [];

    /**
     * holds all properties to be set at the created object
     * 
     * @var ValueInterface[]
     */
    protected array $properties = [];

    /**
     * holds all method calls to be executed at the created object
     * 
     * @var array[]
     */
    protected array $methodCalls = [];

    /**
     * holds the share flag for the service to determine if the resulting object is a singleton
     * 
     * @var bool
     */
    protected bool $shared = false;

    /**
     * holds the instance if the service is being marked as shared
     * 
     * @var null|object
     */
    protected ? object $instance = null;

    /**
     * The constructor
     * 
     * @param string $interface
     */
    public function __construct(string $interface)
    {
        $this->interface = $this->concreteClass = $interface;
    }

    /**
     * returns the interface for this service
     * 
     * @return string
     */
    public function getInterface(): string
    {
        return $this->interface;
    }

    /**
     * sets the concrete class, without validating its existance or compatibility with the interface of the service
     * 
     * @param string $concreteClass
     */
    public function setConcreteClass(string $concreteClass)
    {
        $this->concreteClass = $concreteClass;
    }

    /**
     * returns the concrete class
     * 
     * @return string
     */
    public function getConcreteClass(): string
    {
        return $this->concreteClass;
    }

    /**
     * checks if the concrete class is available (triggers autoloading)
     * 
     * @return bool
     */
    public function isConcreteClassAvailable(): bool
    {
        return class_exists($this->concreteClass, true);
    }

    /**
     * checks if the concrete class does match the interface of the service (triggers autoloading)
     * 
     * @param bool
     */
    public function doesConcreteClassMatchInterface(): bool
    {
        return is_a($this->concreteClass, $this->interface, true);
    }

    /**
     * sets the factory callback for the service without checking its return type compatibility with the
     * interface of the service or the concrete class of the service
     * 
     * @param callable $factory
     */
    public function setFactory(callable $factory)
    {
        $this->factory = $factory;
    }

    /**
     * returns the factory of the service, if any
     */
    public function getFactory(): ? callable
    {
        return $this->factory;
    }

    /**
     * checks whether a factory is assigned to this service or not
     * 
     * @return bool
     */
    public function hasFactory(): bool
    {
        return is_callable($this->factory);
    }

    /**
     * adds an extension to the service which shall be executed after instantiation (if no factory was set) or
     * after the factory has been executed
     * 
     * @param callable $callback
     */
    public function addExtension(callable $callback)
    {
        $this->extensions[] = $callback;
    }

    /**
     * returns an generator for all extensions
     * 
     * @return Generator
     */
    public function generateExtensions(): Generator
    {
        foreach ( $this->extensions as $extension ) {
            yield $extension;
        }
    }

    /**
     * checks whether extensions are set or not
     * 
     * @return bool
     */
    public function hasExtensions(): bool
    {
        return ! empty($this->extensions);
    }

    /**
     * adds an Parameter for the constructor
     * 
     * @param null|string $name
     * @param null|int $position
     * @param $classnameOrValue
     */
    public function addParameter(? string $name, ? int $position, $classnameOrValue)
    {
        $this->parameters[$name] = match($type = gettype($classnameOrValue)) {
            'string' => new ResolverValue($name, $position, $classnameOrValue),
            default => new GenericValue($type, $name, $position, $classnameOrValue)
        };
    }

    /**
     * checks whether the given parameter exists or not
     * 
     * @param string $name
     * @return bool
     */
    public function hasParameter(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * checks if any parameter exists or not
     * 
     * @return bool
     */
    public function hasParameters(): bool
    {
        return ! empty($this->parameters);
    }

    /**
     * returns an generator for all parameters
     * 
     * @return Generator
     */
    public function generateParameters(): Generator
    {
        yield from $this->parameters;
    }

    /**
     * adds a property to be set to the object
     * 
     * @param string $name
     * @param mixed $classnameOrValue
     */
    public function addProperty(string $name, $classnameOrValue)
    {
        $this->properties[$name] = match(gettype($classnameOrValue)) {
            'string' => new ResolverValue($name, null, $classnameOrValue),
            default => new GenericValue($type, $name, null, $classnameOrValue)
        };
    }

    /**
     * checks whether a given property exists
     * 
     * @param string $name
     * @return bool
     */
    public function hasProperty(string $name): bool
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * checks if any property exists
     * 
     * @return bool
     */
    public function hasProperties(): bool
    {
        return ! empty($this->properties);
    }

    /**
     * returns a generator for all properties
     *
     * @return Generator
     */
    public function generateProperties(): Generator
    {
        foreach ( $this->properties as $property ) {
            yield $property;
        }
    }

    /**
     * adds a method call for the service
     * 
     * @param string $method
     * @param array $arguments
     */
    public function addMethodCall(string $method, array $arguments = [])
    {
        $liftedArguments = [];

        foreach ( $arguments as $nameOrPosition => $argument ) {
            $liftedArgument[$nameOrPosition] = match(gettype($classnameOrValue)) {
                'string' => new ResolverValue(
                    $name, 
                    is_int($nameOrPosition) ? $nameOrPosition : null, 
                    is_string($nameOrPosition) ? $nameOrPosition : null, 
                    $classnameOrValue
                ),
                default => new GenericValue($type, $name, null, $classnameOrValue)
            };
        }

        $this->methodCalls[$method] = $liftedArguments;
    }

    /**
     * checks whether the given method call is set or not
     * 
     * @param string $method
     * @return bool
     */
    public function hasMethodCall(string $method): bool
    {
        return array_key_exists($method, $this->methodCalls);
    }

    /**
     * checks if any method call is available or not
     * 
     * @return bool
     */
    public function hasMethodCalls(): bool
    {
        return ! empty($this->methodCalls);
    }

    /**
     * returns a generator for all method calls, every method at the generator will have assigned
     * a generator for the assigned arguments
     * 
     * @return Generator
     */
    public function generateMethodCalls(): Generator
    {
        foreach ( $this->methodCalls as $method => $items ) {
            yield $method => $this->generateMethodCallsArguments($items);
        }
    }

    /**
     * returns the method call arguments generator for the provided array of arguments
     * 
     * @param array $items
     * @return Generator
     */
    protected function generateMethodCallsArguments(array $items): Generator
    {
        foreach ( $items as $item ) {
            yield $item;
        }
    }

    /**
     * sets the shared state for the service
     * 
     * @param bool $switch
     */
    public function setShared(bool $switch)
    {
        $this->shared = $switch;
    }

    /**
     * checks whether the service results into an singleton or not
     * 
     * @return bool
     */
    public function isShared(): bool
    {
        return $this->shared;
    }

    /**
     * sets the provided object as an instance without testing its compatibility towards the interface
     * or concrete class of the service
     * 
     * @param object $object
     */
    public function setInstance(object $object)
    {
        $this->instance = $object;
    }

    /**
     * checks whether a instance is given or not
     * 
     * @return bool
     */
    public function hasInstance(): bool
    {
        return is_object($this->instance);
    }

    /**
     * returns the instance or null if none stored.
     */
    public function getInstance(): ? object
    {
        return $this->instance;
    }

    /**
     * destroys the stored instance, if any
     */
    public function clearInstance()
    {
        $this->instance = null;
    }

}

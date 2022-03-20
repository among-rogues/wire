<?php declare(strict_types=1);
/**
 * This file is part of the Rogue Component Multiverse
 * 
 * (c) 2022 Matthias 'nihylum' Kaschubowski
 * 
 * @package rogue.wire
 */
namespace Rogue\Wire\Value;

use Rogue\Wire\ValueInterface;
use Rogue\Wire\Exception\IncompatibilityException;

/**
 * Resolver Value Class
 * 
 * @author Matthias Kaschubowski <nihylum@gmail.com>
 * @api 1.0
 */
class ResolverValue implements ValueInterface {

    /**
     * holds the type of the value
     * 
     * @var string
     */
    protected string $type = 'resolver';

    /**
     * holds the name of the value, if any.
     * 
     * @var string|null
     */
    protected string|null $name = null;

    /**
     * holds the position of the value, if any.
     * 
     * @var int|null
     */
    protected int|null $position = null;

    /**
     * holds the value, if any.
     * 
     * @var mixed|null
     */
    protected $value = null;

    /**
     * The Constructor.
     * 
     * @param null|string $name
     * @param null|int $position
     * @param mixed $value
     */
    public function __construct(?string $name, ?int $position, $value)
    {
        $this->name = $name;
        $this->position = $position;
        $this->value = $value;
    }

    /**
     * @inherit
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inherit
     */
    public function setPosition(int $position)
    {
        $this->position = $position;
    }

    /**
     * @inherit
     */
    public function getPosition(): int|null
    {
        return $this->position;
    }

    /**
     * @inherit
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @inherit
     */
    public function getName(): string|null
    {
        return $this->name;
    }
    
    /**
     * @inherit
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * @inherit
     */
    public function clearOrigin()
    {
        $this->name = $this->position = null;
    }

    /**
     * @inherit
     * 
     * @throws IncompatibiliyException if the value is not a string or empty
     */
    static public function create($value, ?string $parameterName = null, ?int $position = null): ValueInterface
    {
        if ( ! is_string($value) ) {
            throw new IncompatibilityException('provided value must be type of string, `'.gettype($value).'` given.');
        }

        if ( empty($value) ) {
            throw new IncompatibilityException('provided value can not be empty.');
        }

        return new self($parameterName, $position, $value);
    }

}

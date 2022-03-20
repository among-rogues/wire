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

/**
 * Generic Value Class
 * 
 * @author Matthias Kaschubowski <nihylum@gmail.com>
 * @api 1.0
 */
class GenericValue implements ValueInterface {

    /**
     * holds the type of the value
     * 
     * @var string
     */
    protected string $type = '';
    
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
    public function __construct(string $type, ?string $name, ?int $position, $value)
    {
        $this->type = empty(trim($type)) ? 'unknown' : $type;
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
     */
    static public function create($value, ?string $parameterName = null): ValueInterface
    {
        return new self(gettype($value), $parameterName, null, $int);
    }

}

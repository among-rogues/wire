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
 * Value Interface
 * 
 * @author Matthias Kaschubowski <nihylum@gmail.com>
 * @api 1.0
 */
interface ValueInterface {

    /**
     * returns the type of the value
     */
    public function getType(): string;

    /**
     * sets the position of the value
     * 
     * @param int $position
     */
    public function setPosition(int $position);

    /**
     * returns the position of the value or null.
     * 
     * @return int|null
     */
    public function getPosition(): int|null;

    /**
     * sets the name for the value.
     * 
     * @param string $name
     */
    public function setName(string $name);

    /**
     * returns the name for the value or null.
     * 
     * @return string|null
     */
    public function getName(): string|null;
    
    /**
     * returns the value.
     * 
     * @return mixed
     */
    public function getValue();

    /**
     * clears the origin of the value (position or name).
     */
    public function clearOrigin();

    /**
     * Factory method
     * 
     * @param mixed $value
     * @param null|string $parameterName
     * @return ValueInterface
     */
    static public function create($value, ?string $parameterName = null): ValueInterface;

}

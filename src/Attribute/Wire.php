<?php declare(strict_types=1);
/**
 * This file is part of the Rogue Component Multiverse
 * 
 * (c) 2022 Matthias 'nihylum' Kaschubowski
 * 
 * @package rogue.wire
 */
namespace Rogue\Wire\Attribute;

use Attribute;

/**
 * Wire Attribute Class
 * 
 * @author Matthias Kaschubowski <nihylum@gmail.com>
 * @api 1.0
 */
#[Attribute]
class Wire {

    /**
     * Arguments for the Attribute
     */
    public array $arguments;

    /**
     * The Constructor.
     * 
     * @param mixed[] $arguments
     */
    public function __construct(... $arguments)
    {
        $this->arguments = $arguments;
    }

}

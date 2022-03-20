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
 * Service Provider Interface
 * 
 * @author Matthias Kaschubowski <nihylum@gmail.com>
 * @api 1.0
 */
interface ServiceProviderInterface {

    /**
     * method to register services to the given container.
     * 
     * @param ContainerInterface $container
     * @return void
     */
    public function register(ContainerInterface $container);

}

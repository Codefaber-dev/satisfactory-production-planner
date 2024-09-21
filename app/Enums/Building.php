<?php

namespace App\Enums;

enum Building: string
{
    case SMELTER = 'Smelter';
    case CONSTRUCTOR = 'Constructor';
    case ASSEMBLER = 'Assembler';
    case FOUNDRY = 'Foundry';
    case MANUFACTURER = 'Manufacturer';
    case REFINERY = 'Refinery';
    case PACKAGER = 'Packager';
    case BLENDER = 'Blender';
    case PARTICLE_ACCELERATOR = 'Particle Accelerator';
    case QUANTUM_ENCODER = 'Quantum Encoder';
    case CONVERTER = 'Converter';

    // power generators
    case BIOMASS_BURNER = 'Biomass Burner';
    case COAL_POWERED_GENERATOR = 'Coal-Powered Generator';
    case FUEL_POWERED_GENERATOR = 'Fuel-Powered Generator';
    case GEOTHERMAL_GENERATOR = 'Geothermal Generator';
    case NUCLEAR_POWER_PLANT = 'Nuclear Power Plant';
    case ALIEN_POWER_AUGMENTER = 'Alien Power Augmenter';
}

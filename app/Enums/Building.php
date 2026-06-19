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
    case AWESOME_SINK = 'AWESOME Sink'; // V89 — backs the recycle step (V88)

    // extractors (V77) — names match ExtractorSummary::buildingName exactly
    case MINER_MK1 = 'Miner Mk.1';
    case MINER_MK2 = 'Miner Mk.2';
    case MINER_MK3 = 'Miner Mk.3';
    case WATER_EXTRACTOR = 'Water Extractor';
    case OIL_EXTRACTOR = 'Oil Extractor';
    case RESOURCE_WELL_PRESSURIZER = 'Resource Well Pressurizer';

    // power generators
    case BIOMASS_BURNER = 'Biomass Burner';
    case COAL_GENERATOR = 'Coal Generator';
    case COAL_POWERED_GENERATOR = 'Coal-Powered Generator';
    case FUEL_GENERATOR = 'Fuel Generator';
    case FUEL_POWERED_GENERATOR = 'Fuel-Powered Generator';
    case GEOTHERMAL_GENERATOR = 'Geothermal Generator';
    case NUCLEAR_POWER_PLANT = 'Nuclear Power Plant';
    case ALIEN_POWER_AUGMENTER = 'Alien Power Augmenter';
}

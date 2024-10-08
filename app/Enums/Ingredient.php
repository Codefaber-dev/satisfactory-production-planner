<?php

namespace App\Enums;

enum Ingredient: string
{
    case ACTUAL_SNOW = 'Actual Snow';
    case ADAPTIVE_CONTROL_UNIT = 'Adaptive Control Unit';
    case AI_EXPANSION_SERVER = 'AI Expansion Server';
    case AI_LIMITER = 'AI Limiter';
    case ALCLAD_ALUMINUM_SHEET = 'Alclad Aluminum Sheet';
    case ALIEN_DNA_CAPSULE = 'Alien DNA Capsule';
    case ALIEN_ORGANS = 'Alien Organs';
    case ALIEN_POWER_MATRIX = 'Alien Power Matrix';
    case ALIEN_PROTEIN = 'Alien Protein';
    case ALUMINA_SOLUTION = 'Alumina Solution';
    case ALUMINUM_CASING = 'Aluminum Casing';
    case ALUMINUM_INGOT = 'Aluminum Ingot';
    case ALUMINUM_SCRAP = 'Aluminum Scrap';
    case ASSEMBLY_DIRECTOR_SYSTEM = 'Assembly Director System';
    case AUTOMATED_WIRING = 'Automated Wiring';
    case BALLISTIC_WARP_DRIVE = 'Ballistic Warp Drive';
    case BATTERY = 'Battery';
    case BAUXITE = 'Bauxite';
    case BEACON = 'Beacon';
    case BIOCHEMICAL_SCULPTOR = 'Biochemical Sculptor';
    case BIOMASS = 'Biomass';
    case BLACK_POWDER = 'Black Powder';
    case BLUE_FICSMAS_ORNAMENT = 'Blue FICSMAS Ornament';
    case BLUE_POWER_SLUG = 'Blue Power Slug';
    case CABLE = 'Cable';
    case CANDY_CANE = 'Candy Cane';
    case CATERIUM_INGOT = 'Caterium Ingot';
    case CATERIUM_ORE = 'Caterium Ore';
    case CIRCUIT_BOARD = 'Circuit Board';
    case CLUSTER_NOBELISK = 'Cluster Nobelisk';
    case COAL = 'Coal';
    case COLOR_CARTRIDGE = 'Color Cartridge';
    case COMPACTED_COAL = 'Compacted Coal';
    case COMPUTER = 'Computer';
    case CONCRETE = 'Concrete';
    case COOLING_SYSTEM = 'Cooling System';
    case COPPER_FICSMAS_ORNAMENT = 'Copper FICSMAS Ornament';
    case COPPER_INGOT = 'Copper Ingot';
    case COPPER_ORE = 'Copper Ore';
    case COPPER_POWDER = 'Copper Powder';
    case COPPER_SHEET = 'Copper Sheet';
    case CRUDE_OIL = 'Crude Oil';
    case CRYSTAL_OSCILLATOR = 'Crystal Oscillator';
    case DARK_MATTER_CRYSTAL = 'Dark Matter Crystal';
    case DARK_MATTER_RESIDUE = 'Dark Matter Residue';
    case DIAMONDS = 'Diamonds';
    case DISSOLVED_SILICA = 'Dissolved Silica';
    case ELECTROMAGNETIC_CONTROL_ROD = 'Electromagnetic Control Rod';
    case EMPTY_CANISTER = 'Empty Canister';
    case EMPTY_FLUID_TANK = 'Empty Fluid Tank';
    case ENCASED_INDUSTRIAL_BEAM = 'Encased Industrial Beam';
    case ENCASED_PLUTONIUM_CELL = 'Encased Plutonium Cell';
    case ENCASED_URANIUM_CELL = 'Encased Uranium Cell';
    case EXCITED_PHOTONIC_MATTER = 'Excited Photonic Matter';
    case EXPLOSIVE_REBAR = 'Explosive Rebar';
    case FABRIC = 'Fabric';
    case FACTORY_OUTPUT = 'Factory Output';
    case FANCY_FIREWORKS = 'Fancy Fireworks';
    case FICSITE_INGOT = 'Ficsite Ingot';
    case FICSITE_TRIGON = 'Ficsite Trigon';
    case FICSMAS_BOW = 'FICSMAS Bow';
    case FICSMAS_DECORATION = 'FICSMAS Decoration';
    case FICSMAS_GIFT = 'FICSMAS Gift';
    case FICSMAS_ORNAMENT_BUNDLE = 'FICSMAS Ornament Bundle';
    case FICSMAS_TREE_BRANCH = 'FICSMAS Tree Branch';
    case FICSMAS_WONDER_STAR = 'FICSMAS Wonder Star';
    case FICSONIUM = 'Ficsonium';
    case FICSONIUM_FUEL_ROD = 'Ficsonium Fuel Rod';
    case FLOWER_PETALS = 'Flower Petals';
    case FUEL = 'Fuel';
    case FUSED_MODULAR_FRAME = 'Fused Modular Frame';
    case GAS_FILTER = 'Gas Filter';
    case GAS_NOBELISK = 'Gas Nobelisk';
    case GREEN_POWER_SLUG = 'Green Power Slug';
    case HATCHER_REMAINS = 'Hatcher Remains';
    case HEAT_SINK = 'Heat Sink';
    case HEAVY_MODULAR_FRAME = 'Heavy Modular Frame';
    case HEAVY_OIL_RESIDUE = 'Heavy Oil Residue';
    case HIGH_SPEED_CONNECTOR = 'High-Speed Connector';
    case HOG_REMAINS = 'Hog Remains';
    case HOMING_RIFLE_AMMO = 'Homing Rifle Ammo';
    case IODINE_INFUSED_FILTER = 'Iodine Infused Filter';
    case IONIZED_FUEL = 'Ionized Fuel';
    case IRON_FICSMAS_ORNAMENT = 'Iron FICSMAS Ornament';
    case IRON_INGOT = 'Iron Ingot';
    case IRON_ORE = 'Iron Ore';
    case IRON_PLATE = 'Iron Plate';
    case IRON_REBAR = 'Iron Rebar';
    case IRON_ROD = 'Iron Rod';
    case LEAVES = 'Leaves';
    case LIMESTONE = 'Limestone';
    case LIQUID_BIOFUEL = 'Liquid Biofuel';
    case MAGNETIC_FIELD_GENERATOR = 'Magnetic Field Generator';
    case MODULAR_ENGINE = 'Modular Engine';
    case MODULAR_FRAME = 'Modular Frame';
    case MOTOR = 'Motor';
    case MYCELIA = 'Mycelia';
    case NEURAL_QUANTUM_PROCESSOR = 'Neural-Quantum Processor';
    case NITRIC_ACID = 'Nitric Acid';
    case NITROGEN_GAS = 'Nitrogen Gas';
    case NOBELISK = 'Nobelisk';
    case NON_FISSILE_URANIUM = 'Non-fissile Uranium';
    case NUCLEAR_PASTA = 'Nuclear Pasta';
    case NUKE_NOBELISK = 'Nuke Nobelisk';
    case ORGANIC_DATA_CAPSULE = 'Organic Data Capsule';
    case PACKAGED_ALUMINA_SOLUTION = 'Packaged Alumina Solution';
    case PACKAGED_FUEL = 'Packaged Fuel';
    case PACKAGED_HEAVY_OIL_RESIDUE = 'Packaged Heavy Oil Residue';
    case PACKAGED_IONIZED_FUEL = 'Packaged Ionized Fuel';
    case PACKAGED_LIQUID_BIOFUEL = 'Packaged Liquid Biofuel';
    case PACKAGED_NITRIC_ACID = 'Packaged Nitric Acid';
    case PACKAGED_NITROGEN_GAS = 'Packaged Nitrogen Gas';
    case PACKAGED_OIL = 'Packaged Oil';
    case PACKAGED_ROCKET_FUEL = 'Packaged Rocket Fuel';
    case PACKAGED_SULFURIC_ACID = 'Packaged Sulfuric Acid';
    case PACKAGED_TURBOFUEL = 'Packaged Turbofuel';
    case PACKAGED_WATER = 'Packaged Water';
    case PETROLEUM_COKE = 'Petroleum Coke';
    case PLASMA_SPITTER_REMAINS = 'Plasma Spitter Remains';
    case PLASTIC = 'Plastic';
    case PLUTONIUM_FUEL_ROD = 'Plutonium Fuel Rod';
    case PLUTONIUM_PELLET = 'Plutonium Pellet';
    case PLUTONIUM_WASTE = 'Plutonium Waste';
    case POLYMER_RESIN = 'Polymer Resin';
    case PORTABLE_MINER = 'Portable Miner';
    case POWER_SHARD = 'Power Shard';
    case PRESSURE_CONVERSION_CUBE = 'Pressure Conversion Cube';
    case PULSE_NOBELISK = 'Pulse Nobelisk';
    case PURPLE_POWER_SLUG = 'Purple Power Slug';
    case QUANTUM_SERVER = 'Quantum Server';
    case QUARTZ_CRYSTAL = 'Quartz Crystal';
    case QUICKWIRE = 'Quickwire';
    case RADIO_CONTROL_UNIT = 'Radio Control Unit';
    case RAW_QUARTZ = 'Raw Quartz';
    case REANIMATED_SAM = 'Reanimated SAM';
    case RED_FICSMAS_ORNAMENT = 'Red FICSMAS Ornament';
    case REINFORCED_IRON_PLATE = 'Reinforced Iron Plate';
    case RIFLE_AMMO = 'Rifle Ammo';
    case ROCKET_FUEL = 'Rocket Fuel';
    case ROTOR = 'Rotor';
    case RUBBER = 'Rubber';
    case SAM = 'SAM';
    case SAM_FLUCTUATOR = 'SAM Fluctuator';
    case SCREW = 'Screw';
    case SHATTER_REBAR = 'Shatter Rebar';
    case SILICA = 'Silica';
    case SINGULARITY_CELL = 'Singularity Cell';
    case SMART_PLATING = 'Smart Plating';
    case SMOKELESS_POWDER = 'Smokeless Powder';
    case SNOWBALL = 'Snowball';
    case SOLID_BIOFUEL = 'Solid Biofuel';
    case SPARKLY_FIREWORKS = 'Sparkly Fireworks';
    case STATOR = 'Stator';
    case STEEL_BEAM = 'Steel Beam';
    case STEEL_INGOT = 'Steel Ingot';
    case STEEL_PIPE = 'Steel Pipe';
    case STINGER_REMAINS = 'Stinger Remains';
    case STUN_REBAR = 'Stun Rebar';
    case SULFUR = 'Sulfur';
    case SULFURIC_ACID = 'Sulfuric Acid';
    case SUPERCOMPUTER = 'Supercomputer';
    case SUPERPOSITION_OSCILLATOR = 'Superposition Oscillator';
    case SWEET_FIREWORKS = 'Sweet Fireworks';
    case THERMAL_PROPULSION_ROCKET = 'Thermal Propulsion Rocket';
    case TIME_CRYSTAL = 'Time Crystal';
    case TURBO_MOTOR = 'Turbo Motor';
    case TURBO_RIFLE_AMMO = 'Turbo Rifle Ammo';
    case TURBOFUEL = 'Turbofuel';
    case URANIUM = 'Uranium';
    case URANIUM_FUEL_ROD = 'Uranium Fuel Rod';
    case URANIUM_WASTE = 'Uranium Waste';
    case VERSATILE_FRAMEWORK = 'Versatile Framework';
    case WATER = 'Water';
    case WIRE = 'Wire';
    case WOOD = 'Wood';
    case YELLOW_POWER_SLUG = 'Yellow Power Slug';
}

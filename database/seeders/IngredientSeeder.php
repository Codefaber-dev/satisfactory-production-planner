<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    protected $tier1 = [
        // Minerals
        'Limestone',
        'Raw Quartz',
        'Coal',
        'Sulfur',

        // Ores
        'Iron Ore',
        'Copper Ore',
        'Caterium Ore',
        'SAM',
        'Bauxite',
        'Uranium',

        // Liquids
        'Crude Oil',
        'Water',

        // Gases
        'Nitrogen Gas',

        // Other
        'Leaves',
        'Wood',
        'Flower Petals',
        'Mycelia',
        'Alien Organs',
        'Blue Power Slug',
        'Yellow Power Slug',
        'Purple Power Slug',
        'Hog Remains',
        'Plasma Spitter Remains',
        'Hatcher Remains',
        'Stinger Remains',
    ];

    protected $tier2 = [
        'Concrete',
        'Iron Ingot',
        'Copper Ingot',
        'Caterium Ingot',
        'Steel Ingot',
        'Quartz Crystal',
        'Silica',
        'Polymer Resin',
        'Alumina Solution',
        'Heavy Oil Residue',
        'Fuel',
        'Sulfuric Acid',
        'Black Powder',
        'Plastic',
        'Rubber',
        'Biomass',
        'Compacted Coal',
        'Power Shard',
        'Alien Protein',
        'Organic Data Capsule',
        'Gas Nobelisk',
        'Pulse Nobelisk',
        'Smokeless Powder',
        'Alien DNA Capsule',
        'Diamonds',
        'Excited Photonic Matter',
        'Reanimated SAM',
    ];

    protected $tier3 = [
        'Aluminum Scrap',
        'Petroleum Coke',
        'Copper Powder',
        'Turbofuel',
        'Wire',
        'Iron Rod',
        'Iron Plate',
        'Copper Sheet',
        'Steel Pipe',
        'Steel Beam',
        'Empty Canister',
        'Solid Biofuel',
        'Fabric',
        'Quickwire',
        'Encased Uranium Cell',
        'Nuke Nobelisk',
        'Time Crystal',
    ];

    protected $tier4 = [
        'Aluminum Ingot',
        'Liquid Biofuel',
        'Nitric Acid',
        'Cable',
        'Screw',
        'Encased Industrial Beam',
        'Packaged Water',
        'Packaged Alumina Solution',
        'Packaged Sulfuric Acid',
        'Packaged Nitric Acid',
        'Packaged Nitrogen Gas',
        'Packaged Oil',
        'Packaged Heavy Oil Residue',
        'Packaged Fuel',
        'Packaged Liquid Biofuel',
        'Packaged Turbofuel',
        'Rotor',
        'Stator',
        'Circuit Board',
        'AI Limiter',
        'Nobelisk',
        'Gas Filter',
        'Iron Rebar',
        'Stun Rebar',
        'Shatter Rebar',
        'Explosive Rebar',
        'Cluster Nobelisk',
        'SAM Fluctuator',
    ];

    protected $tier5 = [
        'Aluminum Casing',
        'Empty Fluid Tank',
        'Reinforced Iron Plate',
        'Alclad Aluminum Sheet',
        'Motor',
        'Computer',
        'High-Speed Connector',
        'Electromagnetic Control Rod',
        'Automated Wiring',
        'Dissolved Silica',
        'Ficsite Ingot',
        'Rocket Fuel',
        'Packaged Rocket Fuel',
    ];

    protected $tier6 = [
        'Portable Miner',
        'Crystal Oscillator',
        'Modular Frame',
        'Supercomputer',
        'Battery',
        'Heat Sink',
        'Radio Control Unit',
        'Uranium Fuel Rod',
        'Iodine Infused Filter',
        'Smart Plating',
        'Rifle Ammo',
        'Turbo Rifle Ammo',
        'Homing Rifle Ammo',
        'Ficsite Trigon',
        'Ionized Fuel',
        'Packaged Ionized Fuel',
    ];

    protected $tier7 = [
        'Heavy Modular Frame',
        'Cooling System',
        'Uranium Waste',
        'Versatile Framework',
        'Modular Engine',
        'Neural-Quantum Processor',
    ];

    protected $tier8 = [
        'Fused Modular Frame',
        'Turbo Motor',
        'Non-fissile Uranium',
        'Adaptive Control Unit',
        'Magnetic Field Generator',
        'Quantum Server', // from MK++ Mod
    ];

    protected $tier9 = [
        'Pressure Conversion Cube',
        'Plutonium Pellet',
        'Assembly Director System',
        'Thermal Propulsion Rocket',
    ];

    protected $tier10 = [
        'Encased Plutonium Cell',
        'Nuclear Pasta',
        'Biochemical Sculptor',
    ];

    protected $tier11 = [
        'Plutonium Fuel Rod',
    ];

    protected $tier12 = [
        'Plutonium Waste',
    ];

    protected $tier13 = [
        'Ficsonium',
    ];

    protected $tier14 = [
        'Ficsonium Fuel Rod',
    ];

    protected $tier15 = [
        'Dark Matter Residue',
    ];

    protected $tier16 = [
        'Dark Matter Crystal',
    ];

    protected $tier17 = [
        'Superposition Oscillator',
        'Singularity Cell',
    ];

    protected $tier18 = [
        'Alien Power Matrix',
        'AI Expansion Server',
        'Ballistic Warp Drive',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect($this->tier1)
            ->each(fn ($name) => Ingredient::forceCreate(['name' => $name, 'raw' => true, 'tier' => 1]));

        collect(range(2, 18))
            ->each(function ($num) {
                $tier = "tier{$num}";
                collect($this->$tier)
                    ->each(fn ($name) => Ingredient::forceCreate(['name' => $name, 'raw' => false, 'tier' => $num]));
            });

    }
}

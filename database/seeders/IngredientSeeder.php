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
        'Sulphur',

        // Ores
        'Iron Ore',
        'Copper Ore',
        'Caterium Ore',
        'SAM Ore',
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
        'Alien Carapace',
        'Green Power Slug',
        'Yellow Power Slug',
        'Purple Power Slug'
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
        'Sulphuric Acid',
        'Black Powder',
        'Plastic',
        'Rubber',
        'Biomass',
        'Compacted Coal',
        'Color Cartridge',
        'Power Shard'
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
        'Packaged Sulphuric Acid',
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
        'Spike Rebar'
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
        'Beacon',
        'Automated Wiring'
    ];

    protected $tier6 = [
        'Crystal Oscillator',
        'Modular Frame',
        'Supercomputer',
        'Battery',
        'Heat Sink',
        'Radio Control Unit',
        'Uranium Fuel Rod',
        'Rifle Cartridge',
        'Iodine Infused Filter',
        'Smart Plating'
    ];

    protected $tier7 = [
        'Heavy Modular Frame',
        'Cooling System',
        'Uranium Waste',
        'Versatile Framework',
        'Modular Engine'
    ];

    protected $tier8 = [
        'Fused Modular Frame',
        'Turbo Motor',
        'Non-fissile Uranium',
        'Adaptive Control Unit',
        'Magnetic Field Generator',
        'Quantum Server' // from MK++ Mod
    ];

    protected $tier9 = [
        'Pressure Conversion Cube',
        'Plutonium Pellet',
        'Assembly Director System',
        'Thermal Propulsion Rocket'
    ];

    protected $tier10 = [
        'Encased Plutonium Cell',
        'Nuclear Pasta'
    ];

    protected $tier11 = [
        'Plutonium Fuel Rod',
    ];

    protected $tier12 = [
        'Plutonium Waste'
    ];


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect($this->tier1)
            ->each(fn($name) => Ingredient::forceCreate(['name' => $name, 'raw' => true, 'tier' => 1]));

        collect(range(2,12))
            ->each(function($num) {
                $tier = "tier{$num}";
                collect($this->$tier)
                    ->each(fn($name) => Ingredient::forceCreate(['name' => $name, 'raw' => false, 'tier' => $num]));
            });

    }
}

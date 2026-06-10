<?php

namespace Database\Seeders;

use App\Enums\Ingredient as IngredientEnum;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    protected $tier1 = [
        // Minerals
        IngredientEnum::LIMESTONE->value,
        IngredientEnum::RAW_QUARTZ->value,
        IngredientEnum::COAL->value,
        IngredientEnum::SULFUR->value,

        // Ores
        IngredientEnum::IRON_ORE->value,
        IngredientEnum::COPPER_ORE->value,
        IngredientEnum::CATERIUM_ORE->value,
        IngredientEnum::SAM->value,
        IngredientEnum::BAUXITE->value,
        IngredientEnum::URANIUM->value,

        // Liquids
        IngredientEnum::CRUDE_OIL->value,
        IngredientEnum::WATER->value,

        // Gases
        IngredientEnum::NITROGEN_GAS->value,

        // Other
        IngredientEnum::LEAVES->value,
        IngredientEnum::WOOD->value,
        IngredientEnum::FLOWER_PETALS->value,
        IngredientEnum::MYCELIA->value,
        IngredientEnum::ALIEN_ORGANS->value,
        IngredientEnum::BLUE_POWER_SLUG->value,
        IngredientEnum::YELLOW_POWER_SLUG->value,
        IngredientEnum::PURPLE_POWER_SLUG->value,
        IngredientEnum::HOG_REMAINS->value,
        IngredientEnum::PLASMA_SPITTER_REMAINS->value,
        IngredientEnum::HATCHER_REMAINS->value,
        IngredientEnum::STINGER_REMAINS->value,
    ];

    protected $tier2 = [
        IngredientEnum::CONCRETE->value,
        IngredientEnum::IRON_INGOT->value,
        IngredientEnum::COPPER_INGOT->value,
        IngredientEnum::CATERIUM_INGOT->value,
        IngredientEnum::STEEL_INGOT->value,
        IngredientEnum::QUARTZ_CRYSTAL->value,
        IngredientEnum::SILICA->value,
        IngredientEnum::POLYMER_RESIN->value,
        IngredientEnum::ALUMINA_SOLUTION->value,
        IngredientEnum::HEAVY_OIL_RESIDUE->value,
        IngredientEnum::FUEL->value,
        IngredientEnum::SULFURIC_ACID->value,
        IngredientEnum::BLACK_POWDER->value,
        IngredientEnum::PLASTIC->value,
        IngredientEnum::RUBBER->value,
        IngredientEnum::BIOMASS->value,
        IngredientEnum::COMPACTED_COAL->value,
        IngredientEnum::POWER_SHARD->value,
        IngredientEnum::ALIEN_PROTEIN->value,
        IngredientEnum::ORGANIC_DATA_CAPSULE->value,
        IngredientEnum::GAS_NOBELISK->value,
        IngredientEnum::PULSE_NOBELISK->value,
        IngredientEnum::SMOKELESS_POWDER->value,
        IngredientEnum::ALIEN_DNA_CAPSULE->value,
        IngredientEnum::DIAMONDS->value,
        IngredientEnum::EXCITED_PHOTONIC_MATTER->value,
        IngredientEnum::REANIMATED_SAM->value,
    ];

    protected $tier3 = [
        IngredientEnum::ALUMINUM_SCRAP->value,
        IngredientEnum::PETROLEUM_COKE->value,
        IngredientEnum::COPPER_POWDER->value,
        IngredientEnum::TURBOFUEL->value,
        IngredientEnum::WIRE->value,
        IngredientEnum::IRON_ROD->value,
        IngredientEnum::IRON_PLATE->value,
        IngredientEnum::COPPER_SHEET->value,
        IngredientEnum::STEEL_PIPE->value,
        IngredientEnum::STEEL_BEAM->value,
        IngredientEnum::EMPTY_CANISTER->value,
        IngredientEnum::SOLID_BIOFUEL->value,
        IngredientEnum::FABRIC->value,
        IngredientEnum::QUICKWIRE->value,
        IngredientEnum::ENCASED_URANIUM_CELL->value,
        IngredientEnum::NUKE_NOBELISK->value,
        IngredientEnum::TIME_CRYSTAL->value,
    ];

    protected $tier4 = [
        IngredientEnum::ALUMINUM_INGOT->value,
        IngredientEnum::LIQUID_BIOFUEL->value,
        IngredientEnum::NITRIC_ACID->value,
        IngredientEnum::CABLE->value,
        IngredientEnum::SCREW->value,
        IngredientEnum::ENCASED_INDUSTRIAL_BEAM->value,
        IngredientEnum::PACKAGED_WATER->value,
        IngredientEnum::PACKAGED_ALUMINA_SOLUTION->value,
        IngredientEnum::PACKAGED_SULFURIC_ACID->value,
        IngredientEnum::PACKAGED_NITRIC_ACID->value,
        IngredientEnum::PACKAGED_NITROGEN_GAS->value,
        IngredientEnum::PACKAGED_OIL->value,
        IngredientEnum::PACKAGED_HEAVY_OIL_RESIDUE->value,
        IngredientEnum::PACKAGED_FUEL->value,
        IngredientEnum::PACKAGED_LIQUID_BIOFUEL->value,
        IngredientEnum::PACKAGED_TURBOFUEL->value,
        IngredientEnum::ROTOR->value,
        IngredientEnum::STATOR->value,
        IngredientEnum::CIRCUIT_BOARD->value,
        IngredientEnum::AI_LIMITER->value,
        IngredientEnum::NOBELISK->value,
        IngredientEnum::GAS_FILTER->value,
        IngredientEnum::IRON_REBAR->value,
        IngredientEnum::STUN_REBAR->value,
        IngredientEnum::SHATTER_REBAR->value,
        IngredientEnum::EXPLOSIVE_REBAR->value,
        IngredientEnum::CLUSTER_NOBELISK->value,
        IngredientEnum::SAM_FLUCTUATOR->value,
    ];

    protected $tier5 = [
        IngredientEnum::ALUMINUM_CASING->value,
        IngredientEnum::EMPTY_FLUID_TANK->value,
        IngredientEnum::REINFORCED_IRON_PLATE->value,
        IngredientEnum::ALCLAD_ALUMINUM_SHEET->value,
        IngredientEnum::MOTOR->value,
        IngredientEnum::COMPUTER->value,
        IngredientEnum::HIGH_SPEED_CONNECTOR->value,
        IngredientEnum::ELECTROMAGNETIC_CONTROL_ROD->value,
        IngredientEnum::AUTOMATED_WIRING->value,
        IngredientEnum::DISSOLVED_SILICA->value,
        IngredientEnum::FICSITE_INGOT->value,
        IngredientEnum::ROCKET_FUEL->value,
        IngredientEnum::PACKAGED_ROCKET_FUEL->value,
    ];

    protected $tier6 = [
        IngredientEnum::PORTABLE_MINER->value,
        IngredientEnum::CRYSTAL_OSCILLATOR->value,
        IngredientEnum::MODULAR_FRAME->value,
        IngredientEnum::SUPERCOMPUTER->value,
        IngredientEnum::BATTERY->value,
        IngredientEnum::HEAT_SINK->value,
        IngredientEnum::RADIO_CONTROL_UNIT->value,
        IngredientEnum::URANIUM_FUEL_ROD->value,
        IngredientEnum::IODINE_INFUSED_FILTER->value,
        IngredientEnum::SMART_PLATING->value,
        IngredientEnum::RIFLE_AMMO->value,
        IngredientEnum::TURBO_RIFLE_AMMO->value,
        IngredientEnum::HOMING_RIFLE_AMMO->value,
        IngredientEnum::FICSITE_TRIGON->value,
        IngredientEnum::IONIZED_FUEL->value,
        IngredientEnum::PACKAGED_IONIZED_FUEL->value,
    ];

    protected $tier7 = [
        IngredientEnum::HEAVY_MODULAR_FRAME->value,
        IngredientEnum::COOLING_SYSTEM->value,
        IngredientEnum::URANIUM_WASTE->value,
        IngredientEnum::VERSATILE_FRAMEWORK->value,
        IngredientEnum::MODULAR_ENGINE->value,
        IngredientEnum::NEURAL_QUANTUM_PROCESSOR->value,
    ];

    protected $tier8 = [
        IngredientEnum::FUSED_MODULAR_FRAME->value,
        IngredientEnum::TURBO_MOTOR->value,
        IngredientEnum::NON_FISSILE_URANIUM->value,
        IngredientEnum::ADAPTIVE_CONTROL_UNIT->value,
        IngredientEnum::MAGNETIC_FIELD_GENERATOR->value,
        IngredientEnum::QUANTUM_SERVER->value, // from MK++ Mod
    ];

    protected $tier9 = [
        IngredientEnum::PRESSURE_CONVERSION_CUBE->value,
        IngredientEnum::PLUTONIUM_PELLET->value,
        IngredientEnum::ASSEMBLY_DIRECTOR_SYSTEM->value,
        IngredientEnum::THERMAL_PROPULSION_ROCKET->value,
    ];

    protected $tier10 = [
        IngredientEnum::ENCASED_PLUTONIUM_CELL->value,
        IngredientEnum::NUCLEAR_PASTA->value,
        IngredientEnum::BIOCHEMICAL_SCULPTOR->value,
    ];

    protected $tier11 = [
        IngredientEnum::PLUTONIUM_FUEL_ROD->value,
    ];

    protected $tier12 = [
        IngredientEnum::PLUTONIUM_WASTE->value,
    ];

    protected $tier13 = [
        IngredientEnum::FICSONIUM->value,
    ];

    protected $tier14 = [
        IngredientEnum::FICSONIUM_FUEL_ROD->value,
    ];

    protected $tier15 = [
        IngredientEnum::DARK_MATTER_RESIDUE->value,
    ];

    protected $tier16 = [
        IngredientEnum::DARK_MATTER_CRYSTAL->value,
    ];

    protected $tier17 = [
        IngredientEnum::SUPERPOSITION_OSCILLATOR->value,
        IngredientEnum::SINGULARITY_CELL->value,
    ];

    protected $tier18 = [
        IngredientEnum::ALIEN_POWER_MATRIX->value,
        IngredientEnum::AI_EXPANSION_SERVER->value,
        IngredientEnum::BALLISTIC_WARP_DRIVE->value,
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

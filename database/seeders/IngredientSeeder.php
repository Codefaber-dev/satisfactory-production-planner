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
     * Unpackaged fluids/gases — flow in pipes, NOT sinkable (V65). Their Packaged
     * forms are separate solid items (is_liquid false) and ARE sinkable.
     */
    protected $liquids = [
        IngredientEnum::WATER->value,
        IngredientEnum::CRUDE_OIL->value,
        IngredientEnum::HEAVY_OIL_RESIDUE->value,
        IngredientEnum::FUEL->value,
        IngredientEnum::LIQUID_BIOFUEL->value,
        IngredientEnum::TURBOFUEL->value,
        IngredientEnum::ALUMINA_SOLUTION->value,
        IngredientEnum::SULFURIC_ACID->value,
        IngredientEnum::NITRIC_ACID->value,
        IngredientEnum::DISSOLVED_SILICA->value,
        IngredientEnum::ROCKET_FUEL->value,
        IngredientEnum::IONIZED_FUEL->value,
        IngredientEnum::NITROGEN_GAS->value,
        IngredientEnum::DARK_MATTER_RESIDUE->value,
        IngredientEnum::EXCITED_PHOTONIC_MATTER->value,
    ];

    /**
     * AWESOME Sink points per item (satisfactory.wiki.gg, V65). Curated accurate
     * subset — items absent here keep null (no value → not recyclable, V65).
     * Unpackaged fluids/gases ($liquids) are never sinkable regardless of any value.
     */
    protected $sinkPoints = [
        // raw ores / minerals
        IngredientEnum::IRON_ORE->value => 1,
        IngredientEnum::COPPER_ORE->value => 3,
        IngredientEnum::LIMESTONE->value => 2,
        IngredientEnum::COAL->value => 3,
        IngredientEnum::CATERIUM_ORE->value => 7,
        IngredientEnum::SULFUR->value => 11,
        IngredientEnum::RAW_QUARTZ->value => 15,
        IngredientEnum::BAUXITE->value => 8,
        IngredientEnum::URANIUM->value => 35,

        // ingots
        IngredientEnum::IRON_INGOT->value => 2,
        IngredientEnum::COPPER_INGOT->value => 3,
        IngredientEnum::CATERIUM_INGOT->value => 42,
        IngredientEnum::STEEL_INGOT->value => 8,
        IngredientEnum::ALUMINUM_INGOT->value => 131,

        // basic materials
        IngredientEnum::CONCRETE->value => 12,
        IngredientEnum::QUARTZ_CRYSTAL->value => 50,
        IngredientEnum::SILICA->value => 20,
        IngredientEnum::PLASTIC->value => 75,
        IngredientEnum::RUBBER->value => 60,
        IngredientEnum::PETROLEUM_COKE->value => 20,
        IngredientEnum::COMPACTED_COAL->value => 28,
        IngredientEnum::BIOMASS->value => 12,
        IngredientEnum::ALUMINUM_SCRAP->value => 27,
        IngredientEnum::SOLID_BIOFUEL->value => 48,
        IngredientEnum::POLYMER_RESIN->value => 12,
        IngredientEnum::BLACK_POWDER->value => 14,

        // standard parts
        IngredientEnum::WIRE->value => 6,
        IngredientEnum::CABLE->value => 24,
        IngredientEnum::IRON_ROD->value => 4,
        IngredientEnum::IRON_PLATE->value => 6,
        IngredientEnum::SCREW->value => 2,
        IngredientEnum::COPPER_SHEET->value => 24,
        IngredientEnum::STEEL_PIPE->value => 24,
        IngredientEnum::STEEL_BEAM->value => 64,
        IngredientEnum::QUICKWIRE->value => 17,
        IngredientEnum::EMPTY_CANISTER->value => 60,
        IngredientEnum::EMPTY_FLUID_TANK->value => 225,

        // assembled components
        IngredientEnum::REINFORCED_IRON_PLATE->value => 120,
        IngredientEnum::ROTOR->value => 140,
        IngredientEnum::STATOR->value => 240,
        IngredientEnum::MODULAR_FRAME->value => 408,
        IngredientEnum::SMART_PLATING->value => 520,
        IngredientEnum::ALCLAD_ALUMINUM_SHEET->value => 266,
        IngredientEnum::ALUMINUM_CASING->value => 393,

        // packaged fluids/gases (solid, sinkable — contrast with the unpackaged fluid).
        // satisfactory.wiki.gg AWESOME Sink values.
        IngredientEnum::PACKAGED_WATER->value => 130,
        IngredientEnum::PACKAGED_OIL->value => 180,
        IngredientEnum::PACKAGED_HEAVY_OIL_RESIDUE->value => 180,
        IngredientEnum::PACKAGED_FUEL->value => 270,
        IngredientEnum::PACKAGED_LIQUID_BIOFUEL->value => 370,
        IngredientEnum::PACKAGED_TURBOFUEL->value => 570,
        IngredientEnum::PACKAGED_ALUMINA_SOLUTION->value => 160,
        IngredientEnum::PACKAGED_SULFURIC_ACID->value => 152,
        IngredientEnum::PACKAGED_NITRIC_ACID->value => 412,
        IngredientEnum::PACKAGED_NITROGEN_GAS->value => 312,
        IngredientEnum::PACKAGED_ROCKET_FUEL->value => 1028,
        IngredientEnum::PACKAGED_IONIZED_FUEL->value => 5246,
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect($this->tier1)
            ->each(fn ($name) => Ingredient::forceCreate($this->attrs($name, true, 1)));

        collect(range(2, 18))
            ->each(function ($num) {
                $tier = "tier{$num}";
                collect($this->$tier)
                    ->each(fn ($name) => Ingredient::forceCreate($this->attrs($name, false, $num)));
            });

    }

    /**
     * Backfill is_liquid + sink_points onto already-seeded ingredient rows (V65),
     * without recreating them — for existing databases that predate the columns.
     * Idempotent: re-running applies the current maps. Only touches sink data.
     */
    public function backfill(): void
    {
        Ingredient::query()->each(function (Ingredient $ingredient) {
            $ingredient->is_liquid = in_array($ingredient->name, $this->liquids, true);
            $ingredient->sink_points = $this->sinkPoints[$ingredient->name] ?? null;
            $ingredient->save();
        });
    }

    /**
     * Build the attribute set for one ingredient, including is_liquid + sink_points (V65).
     */
    protected function attrs(string $name, bool $raw, int $tier): array
    {
        return [
            'name' => $name,
            'raw' => $raw,
            'tier' => $tier,
            'is_liquid' => in_array($name, $this->liquids, true),
            'sink_points' => $this->sinkPoints[$name] ?? null,
        ];
    }
}

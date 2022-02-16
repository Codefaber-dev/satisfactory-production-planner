<?php

return [
    // energy cost of raw materials.
    'energy cost' => [
        // assumptions:
        // - normal node
        // - mk.2 miner @ 100% (12MW)
        'Iron Ore' => 12 / 120,
        'Copper Ore' => 12 / 120,
        'Limestone' => 12 / 120,
        'Coal' => 12 / 120,
        'Caterium Ore' => 12 / 120,
        'Raw Quartz' => 12 / 120,
        'Sulfur' => 12 / 120,
        'Bauxite' => 12 / 120,
        'Uranium' => 12 / 120,
        'Water' => 20 / 120, // - water extractor @ 100% (20MW)
        'Crude Oil' => 40 / 120, // - normal node - oil extractor @ 100% (40MW)
        'Nitrogen Gas' => 150 / 60 , // - normal node - resource well pressurizer @ 100% (150MW)
    ],

    // rarity of raw materials derived from standard yield per min / max yield per min with mk.3 miners @100%
    'rarity' => [
        'Iron Ore' => 120 / 35880,
        'Copper Ore' => 120 / 13560,
        'Limestone' => 120 / 25680,
        'Coal' => 120 / 14880,
        'Caterium Ore' => 120 / 5760,
        'Raw Quartz' => 120 / 5040,
        'Sulfur' => 120 / 3240,
        'Bauxite' => 120 / 4920,
        'Uranium' => 120 / 840,
        'Water' => 120 / 1e9,
        'Crude Oil' => 120 / (10*60 + 12*120 + 8*240),
        'Nitrogen Gas' => 120 / (2*30 + 7*60 + 36*120),
    ]
];

<?php

return [
    // energy cost of raw materials.
    'energy cost' => [
        // values in MJ per item = MW * 60s/min / (items/min)
        // assumptions: normal node, mk.2 miner @ 100% (15MW, wiki v1.0), 120/min
        'Iron Ore' => 15 * 60 / 120,        // 7.5 MJ/item
        'Copper Ore' => 15 * 60 / 120,      // 7.5 MJ/item
        'Limestone' => 15 * 60 / 120,       // 7.5 MJ/item
        'Coal' => 15 * 60 / 120,            // 7.5 MJ/item
        'Caterium Ore' => 15 * 60 / 120,    // 7.5 MJ/item
        'Raw Quartz' => 15 * 60 / 120,      // 7.5 MJ/item
        'Sulfur' => 15 * 60 / 120,          // 7.5 MJ/item
        'Bauxite' => 15 * 60 / 120,         // 7.5 MJ/item
        'Uranium' => 15 * 60 / 120,         // 7.5 MJ/item
        'Water' => 20 * 60 / 120,           // 10 MJ/item — water extractor @ 100% (20MW)
        'Crude Oil' => 40 * 60 / 120,       // 20 MJ/item — oil extractor @ 100% (40MW), normal node
        'Nitrogen Gas' => 150 * 60 / 120,   // 75 MJ/item — resource well pressurizer @ 100% (150MW), normal node
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
        'Crude Oil' => 120 / (10 * 60 + 12 * 120 + 8 * 240),
        'Nitrogen Gas' => 120 / (2 * 30 + 7 * 60 + 36 * 120),
    ],
];

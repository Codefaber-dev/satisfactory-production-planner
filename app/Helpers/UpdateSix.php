<?php

namespace App\Helpers;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Building;
use InvalidArgumentException;

class UpdateSix {

    public static function update() 
    {
        echo "<pre>";
        // cleanup deprecated stuff
        static::cleanup();
        echo "Finished cleaning deprecated stuff \n";    

        // seed the ingredients
        static::ingredients();
        echo "Created new ingredients \n";

        // seed the recipes
        static::recipes();
        echo "Created new recipes. \n Done with update.";
    }

    protected static function cleanup() 
    {
        $recipes = [
            'Polyester Fabric',
            'Black Powder',
            'Nobelisk',
            'Crystal Computer'
        ];

        $ingredients = [
            'Alien Carapace',
            'Alien Remains',
            'Rifle Cartridge',
            'Spiked Rebar'
        ];

        // delete deprecated ingredients
        collect($ingredients)->each(function($ing) {
            Ingredient::where('name',$ing)->delete();
        });

        // delete deprecated recipes
        collect($recipes)->each(function($recipe) {
            optional(r($recipe))->delete();
            forgetRecipe($recipe);
        });
        
    }

    protected static function ingredients()
    {

        // ingredients

        $tier1 = [
            'Hog Remains', // i
            'Plasma Spitter Remains', // i
            'Hatcher Remains', // i
            'Stinger Remains' // i
        ];

        $tier2 = [
            'Alien Protein', // r i
            'Organic Data Capsule', // r i
            'Gas Nobelisk', // r i
            'Pulse Nobelisk',  // r i
            'Smokeless Powder', // r i
        ];

        $tier3 = [
            'Nuke Nobelisk', // r i
        ];

        $tier4 = [
            'Cluster Nobelisk', // r i
            'Explosive Rebar', // r i
            'Iron Rebar', // r i
            'Stun Rebar', // r i
            'Shatter Rebar', // r i
        ];

        $tier5 = [];

        $tier6 = [
            'Rifle Ammo', // image, r
            'Turbo Rifle Ammo', //image, r
            'Homing Rifle Ammo' // image, r
        ];


        // seed the ingredients
        collect($tier1)
            ->each(function($name) {
                if (! Ingredient::whereName($name)->exists()) {
                    echo "Creating Ingredient $name \n";
                    Ingredient::forceCreate(['name' => $name, 'raw' => true, 'tier' => 1]);
                }
            });

        collect(range(2,6))
            ->each(function($num) use ($tier2,$tier3,$tier4,$tier5,$tier6) {
                $tier = "tier{$num}";
                collect($$tier)
                    ->each(function($name) use ($num) {
                        if (!Ingredient::whereName($name)->exists()) {
                            echo "Creating Ingredient $name \n";
                            Ingredient::forceCreate(['name' => $name, 'raw' => false, 'tier' => $num]);
                        }
                    });
            });
    }

    protected static function recipes()
    {
        $recipes = [
            "Constructor" => [
                "Alien Protein" => [
                    [
                        "description" => "Hog Protein",
                        "base_yield" => 1,
                        "base_per_min" => 20,
                        "ingredients" => [
                            "Hog Remains" => 20,
                        ],
                        "alt_recipe" => false,
                    ],
                    [
                        "description" => "Spitter Protein",
                        "base_yield" => 1,
                        "base_per_min" => 20,
                        "ingredients" => [
                            "Plasma Spitter Remains" => 20,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => "Hatcher Protein",
                        "base_yield" => 1,
                        "base_per_min" => 20,
                        "ingredients" => [
                            "Hatcher Remains" => 20,
                        ],
                        "alt_recipe" => true,
                    ],
                    [
                        "description" => "Stinger Protein",
                        "base_yield" => 1,
                        "base_per_min" => 20,
                        "ingredients" => [
                            "Stinger Remains" => 20,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                "Biomass" => [
                    [
                        "description" => "Biomass (Alien Protein)",
                        "base_yield" => 100,
                        "base_per_min" => 1500,
                        "ingredients" => [
                            "Alien Protein" => 15,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                "Organic Data Capsule" => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 10,
                        "ingredients" => [
                            "Alien Protein" => 10,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                "Iron Rebar" => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 15,
                        "ingredients" => [
                            "Iron Rod" => 15,
                        ],
                        "alt_recipe" => false,
                    ],
                ]
            ],
            "Assembler" => [
                "Black Powder" => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 30,
                        "ingredients" => [
                            "Coal" => 15,
                            "Sulfur" => 15,
                        ],
                    ],
                ],
                "Cluster Nobelisk" => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 2.5,
                        "ingredients" => [
                            "Nobelisk" => 7.5,
                            "Smokeless Powder" => 10,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                "Computer" => [
                    [
                        "description" => "Crystal Computer",
                        "base_yield" => 3,
                        "base_per_min" => 2.8125,
                        "ingredients" => [
                            "Circuit Board" => 7.5,
                            "Crystal Oscillator" => 2.8125,
                        ],
                        "alt_recipe" => true,
                    ],
                ],
                "Gas Nobelisk" => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 5,
                        "ingredients" => [
                            "Nobelisk" => 5,
                            "Biomass" => 50,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                "Pulse Nobelisk" => [
                    [
                        "base_yield" => 5,
                        "base_per_min" => 5,
                        "ingredients" => [
                            "Nobelisk" => 5,
                            "Crystal Oscillator" => 1,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                "Rifle Ammo" => [
                    [
                        "base_yield" => 15,
                        "base_per_min" => 75,
                        "ingredients" => [
                            "Copper Sheet" => 15,
                            "Smokeless Powder" => 10,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                "Stun Rebar" => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 10,
                        "ingredients" => [
                            "Iron Rebar" => 10,
                            "Quickwire" => 50,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                "Shatter Rebar" => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 10,
                        "ingredients" => [
                            "Iron Rebar" => 20,
                            "Quartz Crystal" => 30,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                "Homing Rifle Ammo" => [
                    [
                        "base_yield" => 10,
                        "base_per_min" => 25,
                        "ingredients" => [
                            "Rifle Ammo" => 50,
                            "High-Speed Connector" => 2.5,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                "Nobelisk" => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 10,
                        "ingredients" => [
                            "Black Powder" => 20,
                            "Steel Pipe" => 20,
                        ],
                    ],
                ]
            ],
            "Refinery" => [
                "Smokeless Powder" => [
                    [
                        "base_yield" => 2,
                        "base_per_min" => 20,
                        "ingredients" => [
                            "Black Powder" => 20,
                            "Heavy Oil Residue" => 10,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                "Fabric" => [
                    [
                        "description" => "Polyester Fabric",
                        "base_yield" => 1,
                        "base_per_min" => 30,
                        "ingredients" => [
                            "Polymer Resin" => 30,
                            "Water" => 30,
                        ],
                        "alt_recipe" => true,
                    ]
                ],
            ],
            "Manufacturer" => [
                "Explosive Rebar" => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 10,
                        "ingredients" => [
                            "Iron Rebar" => 20,
                            "Smokeless Powder" => 20,
                            "Steel Pipe" => 20
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                "Nuke Nobelisk" => [
                    [
                        "base_yield" => 1,
                        "base_per_min" => 0.5,
                        "ingredients" => [
                            "Encased Uranium Cell" => 10,
                            "Nobelisk" => 2.5,
                            "Smokeless Powder" => 5,
                            "AI Limiter" => 3
                        ],
                        "alt_recipe" => false,
                    ],
                ],
                "Turbo Rifle Ammo" => [
                    [
                        "base_yield" => 50,
                        "base_per_min" => 250,
                        "ingredients" => [
                            "Rifle Ammo" => 125,
                            "Aluminum Casing" => 15,
                            "Packaged Turbofuel" => 15,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
            ],
            "Blender" => [
                "Turbo Rifle Ammo" => [
                    [
                        "base_yield" => 50,
                        "base_per_min" => 250,
                        "ingredients" => [
                            "Rifle Ammo" => 125,
                            "Aluminum Casing" => 15,
                            "Turbofuel" => 15,
                        ],
                        "alt_recipe" => false,
                    ],
                ],
            ]
        ];

        collect($recipes)->each(function ($products, $building_name) {
            $building = Building::ofName($building_name);

            collect($products)->each(function ($recipes, $product_name) use ($building) {
                echo "Processing Ingredient: {$product_name} \n";
                
                $product = Ingredient::ofName($product_name);


                // wrap the recipes if needed
                $recipes = isset($recipes[0]) ? $recipes : [$recipes];

                foreach ($recipes as $recipe) {
                    $description = $recipe["description"] ?? $product_name;
                    echo "Processing Recipe: $description \n";

                    $atts = [
                        'building_id' => $building->id,
                        'product_id' => $product->id,
                        'base_yield' => $recipe['base_yield'],
                        'base_per_min' => $recipe['base_per_min'],
                        'alt_recipe' => isset($recipe['alt_recipe']) && (bool) $recipe['alt_recipe'],
                    ];

                    if (isset($recipe['description'])) {
                        $atts['description'] = $recipe['description'];
                    }

                    $recipe_model = Recipe::create($atts);

                    collect($recipe['ingredients'])->each(function ($qty, $name) use ($recipe_model) {

                        $ingredient = Ingredient::ofName($name);

                        if (get_class($ingredient) !== Ingredient::class) {
                            throw new InvalidArgumentException("Could not find ingredient {$name}");
                        }

                        $recipe_model->addIngredient($ingredient, $qty);
                    });

                    if ( isset($recipe['byproducts']))
                        collect($recipe['byproducts'])->each(function ($qty, $name) use ($recipe_model) {

                            $ingredient = Ingredient::ofName($name);

                            if (get_class($ingredient) !== Ingredient::class) {
                                throw new InvalidArgumentException("Could not find ingredient {$name}");
                            }

                            $recipe_model->addByproduct($ingredient, $qty);
                        });

                }
            });
        });
    }
}
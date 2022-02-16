<template>
    <app-layout>
        <template #header>
            <div
                class="flex flex-col justify-center space-y-4 text-xl font-semibold"
            >
                <span>{{
                        factory ? factory.name : "New Production Line"
                    }}</span>

                <div class="flex space-x-2">
                    <span>
                        <input
                            ref="yield"
                            autofocus="autofocus"
                            @change="fetch"
                            type="number"
                            step="0.5"
                            min="0"
                            v-model="newYield"
                            class="w-24 appearance-none rounded py-2 px-1 shadow dark:bg-sky-800"
                        />
                        per min
                    </span>
                    <select
                        @change="setDefaultRecipe"
                        class="rounded py-2 shadow dark:bg-sky-800"
                        v-model="newProduct"
                    >
                        <option
                            :key="option.id"
                            v-for="option in products"
                            :value="option"
                        >
                            {{ option.name }}
                        </option>
                    </select>
                    <select
                        @change="fetch"
                        class="rounded py-2 px-1 shadow dark:bg-sky-800"
                        v-model="newRecipe"
                    >
                        <option
                            :key="option.id"
                            v-for="option in recipes[newProduct.name]"
                            :value="option"
                        >
                            <span v-if="option.favorite">&star;</span>
                            {{ option.description || "default" }}
                        </option>
                    </select>
                    <select
                        @change="fetch"
                        v-model="newVariant"
                        class="rounded py-2 px-1 shadow dark:bg-sky-800"
                    >
                        <option value="mk1">Production mk1 (base)</option>
                        <option value="mk2">Production mk2 (mk++ mod)</option>
                        <option value="mk3">Production mk3 (mk++ mod)</option>
                        <option value="mk4">Production mk4 (mk++ mod)</option>
                    </select>
                    <select
                        @change="fetch"
                        v-model="newBeltSpeed"
                        class="rounded py-2 px-1 shadow dark:bg-sky-800"
                    >
                        <option value="60">Belts mk1 (base)</option>
                        <option value="120">Belts mk2 (base)</option>
                        <option value="270">Belts mk3 (base)</option>
                        <option value="480">Belts mk4 (base)</option>
                        <option value="780">Belts mk5 (base)</option>
                        <option value="2000">
                            Belts mk6 (Covered Conveyer Belt Mod)
                        </option>
                        <option value="7500">
                            Belts mk7 (Covered Conveyer Belt Mod)
                        </option>
                    </select>
                </div>
            </div>
            <div class="my-4 space-x-4">
                <button
                    :disabled="working"
                    @click="saveMyFactory"
                    class="btn btn-emerald"
                >
                    {{
                        factory
                            ? "Save Changes To Factory"
                            : "Save To My Factories"
                    }}
                </button>
                <button
                    @click="
                        diagrams = !diagrams;
                        savePrefs();
                    "
                    class="btn btn-emerald"
                >
                    {{ diagrams ? "✅" : "⬜" }}
                    Toggle Diagrams
                </button>
            </div>
            <div class="mt-4 flex flex-col">
                <hr class="mb-4" />
                <span class="font-semibold">
                    Recipe:
                    <ul class="flex">
                        <li
                            class="px-4 font-medium"
                            v-for="(o, name) in production__productInputs"
                        >
                            {{ name }} ({{ o.base_qty }} per min)
                        </li>
                    </ul>
                </span>
                <span class="font-semibold">
                    Byproducts: {{ production__productByproducts }}
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto flex sm:px-6 lg:px-8">
                <div
                    v-if="done && production"
                    class="relative flex flex-1 flex-col space-y-2 p-4 dark:text-gray-100"
                >
                    <production-warning :production__warnings="production__warnings" :show-warnings="showWarnings" />

                    <div class="flex flex-1 space-x-8 py-4">
                        <!-- Left Side -->
                        <div
                            class="flex w-96 flex-col rounded-lg border border-gray-500 bg-white text-sm shadow-lg dark:border-sky-700 dark:bg-slate-900"
                        >
                            <div
                                class="rounded-t-lg bg-gray-900 p-4 text-center text-xl font-semibold text-white dark:bg-sky-700"
                            >
                                Production Summary
                            </div>
                            <table class="">
                                <template
                                    v-if="production.raw.length"
                                >
                                    <tr class="bg-sky-300 dark:bg-sky-800">
                                        <th
                                            class="text-lg font-semibold"
                                            colspan="3"
                                        >
                                            Raw Materials (per min)
                                        </th>
                                    </tr>
                                    <tr
                                        v-for="material in production.raw"
                                    >
                                        <td colspan="2" class="p-2">
                                            <cloud-image
                                                class="inline-flex"
                                                :public-id="`${material.name}.png`"
                                                crop="scale"
                                                quality="100"
                                                width="32"
                                                :alt="material.name"
                                            />
                                            {{ material.name }}
                                        </td>
                                        <td class="p-2 text-right">
                                            <div
                                                class="flex justify-end space-x-2"
                                            >
                                                <button
                                                    @click="
                                                        disabledRawMaterials[
                                                            material.name
                                                        ] =
                                                            !!!disabledRawMaterials[
                                                                material.name
                                                            ]
                                                    "
                                                    class="btn-sm btn-emerald"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="h-6 w-6"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                                                        />
                                                    </svg>
                                                </button>
                                                <input
                                                    :disabled="
                                                        disabledRawMaterials[
                                                            material.name
                                                        ]
                                                    "
                                                    @input="
                                                        rawUnchanged = false
                                                    "
                                                    class="w-24 rounded bg-gray-200 p-2 text-right text-sm disabled:cursor-not-allowed disabled:bg-gray-600 dark:bg-sky-200 dark:text-slate-900 dark:disabled:bg-gray-600"
                                                    v-model="
                                                        rawMaterials[
                                                            material.name
                                                        ]
                                                    "
                                                    :rel="material.name"
                                                    type="text"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-center">
                                            <button
                                                @click="helpRawMaterials"
                                                class="btn btn-gray mb-4 mr-2"
                                            >
                                                Help
                                            </button>
                                            <button
                                                @click="fetchNewYield"
                                                :disabled="rawUnchanged"
                                                class="btn btn-emerald mb-4"
                                            >
                                                Update Yield
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <template
                                    v-if="
                                        production.intermediate.length
                                    "
                                >
                                    <tr class="bg-sky-300 dark:bg-sky-800">
                                        <th
                                            class="text-lg font-semibold"
                                            colspan="3"
                                        >
                                            Intermediate Products
                                        </th>
                                    </tr>
                                    <tr
                                        v-for="material in production.intermediate"
                                    >
                                        <td colspan="2" class="p-2">
                                            <div class="flex">
                                                <cloud-image
                                                    class="mr-2 inline-flex"
                                                    :public-id="`${material.name}.png`"
                                                    crop="scale"
                                                    quality="100"
                                                    width="32"
                                                    :alt="material.name"
                                                />
                                                <div>
                                                    <span>{{
                                                            material.name
                                                        }}</span>
                                                    <br />
                                                    <span class="italic"
                                                    >{{ material.qty }} per
                                                        min</span
                                                    >
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-2 text-right">
                                            <div class="flex space-x-2">
                                                <label
                                                    :for="`importToggle${material.name.replace(/ /gi,'')}`"
                                                    class="flex cursor-pointer items-center"
                                                >
                                                    <!-- Produce -->
                                                    <div
                                                        class="mr-2 font-medium"
                                                    >
                                                        Produce
                                                    </div>
                                                    <!-- toggle -->
                                                    <div class="relative">
                                                        <!-- input -->
                                                        <input
                                                            :id="`importToggle${material.name.replace(/ /gi,'')}`"
                                                            v-model="newImports[material.name]"
                                                            type="checkbox"
                                                            class="sr-only"
                                                        />
                                                        <!-- line -->
                                                        <div
                                                            class="block h-8 w-14 rounded-full bg-gray-600 dark:bg-gray-200"
                                                        ></div>
                                                        <!-- dot -->
                                                        <div
                                                            class="dot absolute left-1 top-1 h-6 w-6 rounded-full bg-white transition dark:bg-gray-800"
                                                        ></div>
                                                    </div>
                                                    <!-- import -->
                                                    <div
                                                        class="ml-2 font-medium"
                                                    >
                                                        Import
                                                    </div>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-center">
                                            <button
                                                @click="helpImport"
                                                class="btn btn-gray mr-2"
                                            >
                                                Help
                                            </button>
                                            <button
                                                @click="fetch"
                                                class="btn btn-emerald"
                                            >
                                                Recalculate
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <tr>
                                    <td>&nbsp;</td>
                                </tr>

                                <tr class="bg-sky-300 dark:bg-sky-700">
                                    <th
                                        class="text-lg font-semibold"
                                        colspan="3"
                                    >
                                        Power Summary
                                    </th>
                                </tr>
                                <tr>
                                    <td
                                        colspan="2"
                                        class="whitespace-nowrap p-2"
                                    >
                                        Energy Per Product (MJ)
                                    </td>
                                    <td class="p-2 text-right">
                                        {{ Math.round((100 * production__total_power) / newYield / 60) / 2 }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="p-2">
                                        Total Power Used (MW)
                                    </td>
                                    <td class="p-2 text-right">
                                        {{ production__total_power }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="p-2">
                                        Coal Generator Equiv.
                                    </td>
                                    <td class="p-2 text-right">
                                        {{ Math.ceil(production__total_power / 75) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="p-2">
                                        Fuel Generator Equiv.
                                    </td>
                                    <td class="p-2 text-right">
                                        {{ Math.ceil( production__total_power / 150 ) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="p-2">
                                        Nuclear Power Plant Equiv.
                                    </td>
                                    <td class="p-2 text-right">
                                        {{ Math.ceil( production__total_power / 2500 ) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>&nbsp;</td>
                                </tr>

                                <tr class="bg-sky-300 dark:bg-sky-700">
                                    <th
                                        class="text-lg font-semibold"
                                        colspan="3"
                                    >
                                        Parts List (Buildings)
                                    </th>
                                </tr>
                                <tr
                                    @click="
                                        buildingChecks[mat] = !buildingChecks[mat]
                                    "
                                    class="cursor-pointer"
                                    v-for="( num, mat ) in production__building_summary.total_build_cost"
                                >
                                    <td
                                        colspan="2"
                                        class="whitespace-nowrap p-2"
                                    >
                                        <cloud-image
                                            class="mr-2 inline-flex"
                                            :public-id="mat"
                                            crop="scale"
                                            quality="100"
                                            width="24"
                                            :alt="mat"
                                        />
                                        <input
                                            v-model="buildingChecks[mat]"
                                            type="checkbox"
                                        />
                                        {{ mat }}
                                    </td>
                                    <td class="p-2 text-right">{{ num }}</td>
                                </tr>
                            </table>
                        </div>
                        <!-- middle -->
                        <div
                            class="flex flex-1 flex-col rounded-lg border border-gray-500 bg-white text-sm shadow-lg dark:border-sky-700 dark:bg-slate-900"
                        >
                            <div
                                class="rounded-t-lg bg-gray-900 p-4 text-center text-xl font-semibold text-white dark:bg-sky-700"
                            >
                                Intermediate Products
                            </div>
                            <table>
                                <tr>
                                    <!--                                    <th class="font-semibold">Done</th>-->
                                    <th class="font-semibold">Product</th>
                                    <th class="font-semibold">Inputs</th>
                                    <th class="font-semibold">Recipe</th>
                                    <th class="font-semibold">Production</th>
                                </tr>
                                <tr v-show="Object.values(productionChecks).some( (o) => o )">
                                    <th
                                        class="bg-blue-200 p-2 text-center dark:bg-sky-600"
                                        colspan="100"
                                    > {{ hideCompleted ? "Hiding" : "Showing"  }}
                                        {{ Object.values( productionChecks ).filter((o) => o).length  }}
                                        completed rows
                                        <button @click="hideCompleted = !hideCompleted"
                                                class="rounded bg-emerald-500 px-4 py-2 text-sm hover:bg-emerald-600 focus:bg-emerald-700"
                                        >
                                            Toggle Completed
                                        </button>
                                    </th>
                                </tr>
                                <template
                                    v-for="(level, index) in production.parsed"
                                >
                                    <tr>
                                        <th
                                            class="bg-blue-200 py-2 dark:bg-slate-800"
                                            colspan="100"
                                        >
                                            {{ index }}
                                        </th>
                                    </tr>

                                    <template v-for="material in level">
                                        <tbody
                                            v-for="recipe in material"
                                            v-show="
                                                !hideCompleted ||
                                                !productionChecks[material.name + '-' + recipe.description]
                                            "
                                            :class="[productionChecks[recipe.name + '-' + recipe.description] ? 'opacity-25' : 'opacity-100']"
                                        >
                                        <tr
                                            class="border-t border-gray-200 dark:border-slate-700"
                                        >
                                            <!--                                    <td class="text-center">-->
                                            <!--                                        <input v-model="productionChecks[material.name]" class="mr-1" type="checkbox">                                    </td>-->

                                            <td class="p-2">
                                                <div
                                                    @click="toggleProductionCheck(recipe.name + '-' + recipe.description)"
                                                    class="flex cursor-pointer items-center rounded-lg border border-teal-500 bg-teal-200 p-2 shadow-lg dark:text-slate-800"
                                                >
                                                    <cloud-image
                                                        class="mr-2"
                                                        :public-id="recipe.name"
                                                        width="48"
                                                        crop="scale"
                                                        :alt="recipe.name"
                                                    />

                                                    <div
                                                        class="flex w-full flex-col space-y-2"
                                                    >
                                                            <span
                                                                class="font-semibold"
                                                            >
                                                                {{
                                                                    recipe.name
                                                                }}
                                                            </span>
                                                        <span
                                                            class="font-light"
                                                        >
                                                                {{
                                                                recipe.qty
                                                            }}
                                                                per min
                                                            </span>
                                                        <div
                                                            v-if="
                                                                    Object.keys(
                                                                        recipe.outputs ||
                                                                            {}
                                                                    ).length
                                                                "
                                                            class="flex w-full flex-col rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg"
                                                        >
                                                                <span
                                                                    class="font-semibold"
                                                                >
                                                                    Destination
                                                                </span>
                                                            <span
                                                                v-for="(
                                                                        qty, mat
                                                                    ) in recipe.outputs"
                                                            >
                                                                    <cloud-image
                                                                        class="mr-2 inline-flex"
                                                                        :public-id="
                                                                            mat
                                                                        "
                                                                        width="32"
                                                                        crop="scale"
                                                                        :alt="
                                                                            mat
                                                                        "
                                                                    />
                                                                <!--                                                            {{ mat }} -->
                                                                    {{
                                                                    Math.round(
                                                                        (100 *
                                                                            100 *
                                                                            qty) /
                                                                        recipe.qty
                                                                    ) / 100
                                                                }}%
                                                                </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td nowrap class="p-2">
                                                <div
                                                    class="rounded-lg border border-yellow-500 bg-yellow-200 p-2 shadow-lg dark:text-slate-800"
                                                >
                                                    <div
                                                        v-if="
                                                                production
                                                                    .recipes[
                                                                    material
                                                                        .name
                                                                ]
                                                            "
                                                        class="my-2 flex items-center"
                                                        v-for="(
                                                                ing, name
                                                            ) in production
                                                                .recipes[
                                                                material.name
                                                            ].inputs"
                                                    >
                                                        <cloud-image
                                                            class="mr-2"
                                                            :public-id="
                                                                    name
                                                                "
                                                            width="48"
                                                            crop="scale"
                                                            :alt="name"
                                                        />
                                                        <span
                                                            class="font-semibold"
                                                        >{{ name }}
                                                                <span
                                                                    v-if="
                                                                        imports[
                                                                            name
                                                                        ]
                                                                    "
                                                                    class="rounded-lg bg-green-300 px-2 py-1 text-xs"
                                                                >Imported</span
                                                                >
                                                                <br />
                                                                <span
                                                                    class="font-light"
                                                                >{{
                                                                        Math.round(
                                                                            10000 *
                                                                            ing.needed_qty
                                                                        ) /
                                                                        10000
                                                                    }}
                                                                    per min
                                                                </span>
                                                                <br />
                                                                <span
                                                                    class="font-light italic"
                                                                >
                                                                    {{
                                                                        Math.round(
                                                                            (100 *
                                                                                100 *
                                                                                ing.needed_qty) /
                                                                            production
                                                                                .partsPerMinuteAll[
                                                                                name
                                                                                ]
                                                                        ) / 100
                                                                    }}%
                                                                </span>
                                                            </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-2">
                                                <!-- material is end product -->
                                                <template
                                                    v-if="
                                                            material.name ===
                                                            newProduct.name
                                                        "
                                                >
                                                    <div
                                                        class="flex flex-col"
                                                    >
                                                        <recipe-picker
                                                            @select="
                                                                    selectNewRecipe
                                                                "
                                                            :recipes="
                                                                    recipes[
                                                                        newProduct
                                                                            .name
                                                                    ]
                                                                "
                                                            :selected="
                                                                    newRecipe
                                                                "
                                                        ></recipe-picker>
                                                    </div>
                                                </template>

                                                <!-- everything else -->
                                                <template v-else>
                                                    <recipe-picker
                                                        @select="
                                                                selectNewSubRecipe
                                                            "
                                                        :recipes="
                                                                recipes[
                                                                    material
                                                                        .name
                                                                ]
                                                            "
                                                        :selected="
                                                                production
                                                                    .recipe_models[
                                                                    material
                                                                        .name
                                                                ]
                                                            "
                                                    ></recipe-picker>
                                                </template>
                                            </td>

                                            <td
                                                v-if="
                                                        production.recipes[
                                                            material.name
                                                        ]
                                                    "
                                                class="p-2"
                                            >
                                                <select
                                                    v-model="
                                                            production.recipes[
                                                                material.name
                                                            ].selected_variant
                                                        "
                                                    class="w-full rounded py-2 text-right shadow dark:bg-sky-800"
                                                >
                                                    <option
                                                        class="text-right"
                                                        :value="mk"
                                                        v-for="(
                                                                opt, mk
                                                            ) in production
                                                                .recipes[
                                                                material.name
                                                            ].building_details"
                                                    >
                                                        {{
                                                            opt.num_buildings
                                                        }}x {{ mk }} @{{
                                                            opt.clock_speed
                                                        }}% [{{
                                                            Math.round(
                                                                opt.power_usage
                                                            )
                                                        }}
                                                        MW]
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr v-show="diagrams">
                                            <td
                                                class="text-center"
                                                colspan="100"
                                            >
                                                <div
                                                    class="flex justify-end space-x-8"
                                                >
                                                    <div
                                                        class="w-48 text-left"
                                                    >
                                                        <ul>
                                                            <li
                                                                class="flex border-b border-gray-300"
                                                            >
                                                                    <span
                                                                        class="ml-2 font-semibold"
                                                                    >Foundations</span
                                                                    >
                                                                <span
                                                                    class="flex-1 text-right"
                                                                >{{
                                                                        getFootprint(
                                                                            material.name
                                                                        )
                                                                            .foundations
                                                                    }}
                                                                        ({{
                                                                        getFootprint(
                                                                            material.name
                                                                        )
                                                                            .length_foundations
                                                                    }}
                                                                        x
                                                                        {{
                                                                        getFootprint(
                                                                            material.name
                                                                        )
                                                                            .width_foundations
                                                                    }})</span
                                                                >
                                                            </li>
                                                            <li
                                                                class="flex border-b border-gray-300"
                                                            >
                                                                    <span
                                                                        class="ml-2 font-semibold"
                                                                    >Walls</span
                                                                    >
                                                                <span
                                                                    class="flex-1 text-right"
                                                                >{{
                                                                        getFootprint(
                                                                            material.name
                                                                        )
                                                                            .walls
                                                                    }}
                                                                        ({{
                                                                        getFootprint(
                                                                            material.name
                                                                        )
                                                                            .height_walls
                                                                    }}
                                                                        x
                                                                        {{
                                                                        2 *
                                                                        (getFootprint(
                                                                                material.name
                                                                            )
                                                                                .length_foundations +
                                                                            getFootprint(
                                                                                material.name
                                                                            )
                                                                                .width_foundations)
                                                                    }})</span
                                                                >
                                                            </li>
                                                            <li
                                                                class="flex border-b border-gray-300"
                                                            >
                                                                    <span
                                                                        class="ml-2 font-semibold"
                                                                    >Building
                                                                        Rows</span
                                                                    >
                                                                <span
                                                                    class="flex-1 text-right"
                                                                >{{
                                                                        getFootprint(
                                                                            material.name
                                                                        )
                                                                            .rows
                                                                    }}
                                                                    </span>
                                                            </li>
                                                            <li
                                                                class="flex border-b border-gray-300"
                                                            >
                                                                    <span
                                                                        class="ml-2 font-semibold"
                                                                    >Buildings
                                                                        Per
                                                                        Row</span
                                                                    >
                                                                <span
                                                                    class="flex-1 text-right"
                                                                >{{
                                                                        getFootprint(
                                                                            material.name
                                                                        )
                                                                            .buildings_per_row
                                                                    }}
                                                                    </span>
                                                            </li>
                                                            <li
                                                                class="flex border-b border-gray-300"
                                                            >
                                                                    <span
                                                                        class="ml-2 font-semibold"
                                                                    >Belt
                                                                        Speed</span
                                                                    >
                                                                <span
                                                                    class="flex-1 text-right"
                                                                >{{
                                                                        getFootprint(
                                                                            material.name
                                                                        )
                                                                            .belt_speed
                                                                    }}
                                                                    </span>
                                                            </li>
                                                        </ul>
                                                        <!--                                                <pre>{{ getFootprint(material.name) }}</pre>-->
                                                    </div>
                                                    <div
                                                        class="flex justify-center p-2"
                                                    >
                                                        <div
                                                            style="
                                                                    box-sizing: content-box;
                                                                "
                                                            :style="{
                                                                    height:
                                                                        getFootprint(
                                                                            material.name
                                                                        )
                                                                            .length_foundations *
                                                                            2 +
                                                                        'rem',
                                                                    width:
                                                                        getFootprint(
                                                                            material.name
                                                                        )
                                                                            .width_foundations *
                                                                            2 +
                                                                        'rem',
                                                                }"
                                                            class="relative flex items-start justify-center bg-blue-300 text-xl shadow-lg"
                                                        >
                                                            <!--                                            <div :key="stat" v-for="(num,stat) in getFootprint(material.name).footprint">{{ stat }} {{ num }}</div>-->
                                                            <!--                                                <div class="text-blue-500">Foundations-->
                                                            <!--                                                    {{ getFootprint(material.name).length_foundations }} x-->
                                                            <!--                                                    {{ getFootprint(material.name).width_foundations }}-->
                                                            <!--                                                </div>-->
                                                            <div
                                                                style="
                                                                        opacity: 0.3;
                                                                        box-sizing: content-box;
                                                                    "
                                                                class="absolute flex h-full w-full flex-wrap items-center justify-center"
                                                            >
                                                                <template
                                                                    v-for="ii in Array(
                                                                            getFootprint(
                                                                                material.name
                                                                            )
                                                                                .foundations
                                                                        )"
                                                                >
                                                                    <div
                                                                        class="border border-blue-500"
                                                                        style="
                                                                                box-sizing: border-box;
                                                                                height: 2rem;
                                                                                width: 2rem;
                                                                            "
                                                                    ></div>
                                                                </template>
                                                            </div>
                                                            <div
                                                                style="
                                                                        padding: 2rem;
                                                                    "
                                                                class="absolute flex h-full w-full flex-wrap items-center justify-center"
                                                            >
                                                                <div
                                                                    class="flex w-full items-center justify-center"
                                                                    v-for="(
                                                                            ii,
                                                                            row
                                                                        ) in Array(
                                                                            getFootprint(
                                                                                material.name
                                                                            )
                                                                                .rows
                                                                        )"
                                                                >
                                                                    <div
                                                                        v-for="(
                                                                                jj,
                                                                                col
                                                                            ) in Array(
                                                                                getFootprint(
                                                                                    material.name
                                                                                )
                                                                                    .buildings_per_row
                                                                            )"
                                                                        :style="{
                                                                                height:
                                                                                    getFootprint(
                                                                                        material.name
                                                                                    )
                                                                                        .building_length /
                                                                                        4 +
                                                                                    'rem',
                                                                                width:
                                                                                    getFootprint(
                                                                                        material.name
                                                                                    )
                                                                                        .building_width /
                                                                                        4 +
                                                                                    'rem',
                                                                            }"
                                                                        :class="
                                                                                1 +
                                                                                    col +
                                                                                    row *
                                                                                        getFootprint(
                                                                                            material.name
                                                                                        )
                                                                                            .buildings_per_row <=
                                                                                getFootprint(
                                                                                    material.name
                                                                                )
                                                                                    .num_buildings
                                                                                    ? [
                                                                                          'border-blue-800',
                                                                                          'bg-blue-800',
                                                                                      ]
                                                                                    : [
                                                                                          'border-transparent',
                                                                                          'text-transparent',
                                                                                          'bg-transparent',
                                                                                      ]
                                                                            "
                                                                        style="
                                                                                box-sizing: border-box;
                                                                            "
                                                                        class="flex items-center justify-center rounded border bg-opacity-25 text-xs"
                                                                    >
                                                                        {{
                                                                            getFootprint(
                                                                                material.name
                                                                            )
                                                                                .monogram
                                                                        }}
                                                                        <!--                                                            {{ 1+col+(row*getFootprint(material.name).buildings_per_row) }}-->
                                                                        <!--                                                            {{ getFootprint(material.name).num_buildings }}-->
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </template>
                                </template>
                            </table>
                        </div>
                        <!-- right -->
                        <div
                            class="flex-col rounded-lg border border-gray-500 bg-white text-sm shadow-lg dark:border-sky-700 dark:bg-slate-900"
                        >
                            <div
                                class="rounded-t-lg bg-gray-900 p-4 text-center text-xl font-semibold text-white dark:bg-sky-700"
                            >
                                Building Summary
                            </div>
                            <table>
                                <tr>
                                    <th>Building</th>
                                    <th>Num</th>
                                    <th>Power Usage (MW)</th>
                                    <th>Build Cost</th>
                                    <!--                                    <th>Footprint</th>-->
                                </tr>
                                <tbody
                                    class="border-b border-gray-200 dark:border-slate-800"
                                    v-for="(
                                        o, bldg
                                    ) in production__building_summary.variants"
                                >
                                <tr>
                                    <td class="p-2">{{ bldg }}</td>
                                    <td class="p-2">
                                        {{ o.num_buildings }}
                                    </td>
                                    <td class="p-2 text-right">
                                        {{ Math.round(o.power_usage) }}
                                    </td>
                                    <td nowrap="" class="p-2 text-right">
                                        <div class="flex flex-col">
                                            <div
                                                :key="mat"
                                                v-for="(
                                                        num, mat
                                                    ) in o.build_cost"
                                            >
                                                {{ mat }} {{ num }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                                <tbody
                                    class="rounded-b-lg bg-blue-200 font-bold dark:bg-gray-900"
                                >
                                <tr>
                                    <td class="p-2">Total</td>
                                    <td class="p-2">
                                        {{ production__total_buildings }}
                                    </td>
                                    <td class="p-2 text-right">
                                        {{ production__total_power }}
                                    </td>
                                    <td nowrap="" class="p-2 text-right">
                                        <div class="flex flex-col">
                                            <div
                                                :key="mat"
                                                v-for="(
                                                        num, mat
                                                    ) in production__building_summary.total_build_cost"
                                            >
                                                {{ mat }} {{ num }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--            <div v-if="done && production" class="w-3/4 mx-auto sm:px-6 lg:px-8 flex space-x-10 mt-4">-->
            <!--                <div class="w-1/5"></div>-->
            <!--                <div class="bg-white dark:bg-gray-800 dark:text-gray-100 shadow-xl sm:rounded-lg p-4 flex space-x-4 flex-1">-->
            <!--                    <div class="w-3/5 flex flex-col text-sm">-->

            <!--                    </div>-->
            <!--                    <div class="flex flex-col flex-1 text-sm">-->

            <!--                    </div>-->
            <!--                </div>-->
            <!--            </div>-->
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import RecipePicker from "@/Components/RecipePicker";
import store from "@/store";
import ProductionWarning from "@/Pages/Production/ProductionWarning";

export default {
    props: [
        "products",
        "recipes",
        "favorites",
        "production",
        "product",
        "recipe",
        "yield",
        "variant",
        "belt_speed",
        "constraints",
        "factory",
        "imports"
    ],
    components: {
        ProductionWarning,
        AppLayout,
        RecipePicker
    },

    mounted() {
        window.Page = this;

        this.$refs.yield.focus();

        this.newYield = this.production.yield;
    },

    data() {
        return {
            done: true,
            working: false,
            newYield: this.yield,
            newProduct: this.product,
            newRecipe: this.recipe,
            newVariant: this.variant,
            // recipe_models: this.production.recipe_models,
            newBeltSpeed: this.belt_speed || 780,
            // production_recipes : this.production.recipes,
            productionChecks: {},
            buildingChecks: {},
            hideCompleted: true,
            rawMaterials: {},
            intermediateMaterials: {},
            disabledRawMaterials: {},
            newConstraints: [],
            rawUnchanged: true,
            diagrams: store.getItem("diagrams", true),
            newImports: this.imports,
            showWarnings: true
        };
    },

    computed: {

        production__warnings() {
            if (!this.production) return [];
            return this.production.warnings;
        },

        production__productName() {
            if (!this.production) return false;
            return this.production.product;
        },

        production__productYield() {
            if (!this.production) return false;
            return this.production.yield;
        },

        production__productRecipe() {
            if (!this.production) return false;
            return this.production.recipe;
        },

        production__productInputs() {
            if (!this.production) return false;

            return this.production.recipes[this.production__productName].inputs;
        },

        production__productByproducts() {
            if (!this.production) return false;

            let b = this.production["byproducts per minute"],
                r = [];

            if (b.hasOwnProperty("length") && !b.length) return "n/a";

            for (let prop in b) {
                if (b.hasOwnProperty(prop))
                    r.push(`${prop} - ${b[prop]} per min`);
            }

            return r.join(", ");
        },

        production__building_details() {
            let ret = [];

            for (let prop in this.production.recipes) {
                if (this.production.recipes.hasOwnProperty(prop))
                    ret.push(this.production.recipes[prop]);
            }

            return ret.map((o) =>
                Object.assign(o.building_details[o.selected_variant], {
                    variant: o.selected_variant
                })
            );
        },

        production__total_power() {
            return Math.round(
                this.production__building_details
                    .map((o) => +o.power_usage)
                    .reduce((a, b) => a + b, 0)
            );
        },

        production__total_buildings() {
            return Math.round(
                this.production__building_details
                    .map((o) => +o.num_buildings)
                    .reduce((a, b) => a + b, 0)
            );
        },

        production__building_summary() {
            let ret = {};
            ret.total_build_cost = {};
            ret.variants = {};
            this.production__building_details.forEach((o) => {
                if (!ret.variants.hasOwnProperty(o.variant))
                    ret.variants[o.variant] = Object.assign({}, o);
                else {
                    ret.variants[o.variant].num_buildings += o.num_buildings;
                    ret.variants[o.variant].power_usage += o.power_usage;
                }

                for (let prop in o.build_cost) {
                    // increment the build cost for the particular building
                    if (ret.variants[o.variant].build_cost.hasOwnProperty(prop))
                        ret.variants[o.variant].build_cost[prop] +=
                            o.build_cost[prop];
                    else
                        ret.variants[o.variant].build_cost[prop] =
                            o.build_cost[prop];

                    // increment the total build cost
                    if (ret.total_build_cost.hasOwnProperty(prop))
                        ret.total_build_cost[prop] += o.build_cost[prop];
                    else ret.total_build_cost[prop] = o.build_cost[prop];
                }
            });

            return ret;
        }
    },

    methods: {
        async fetch() {
            if (this.yield < 1) return false;

            this.working = true;

            let parts = [
                    "dashboard",
                    this.newProduct.name,
                    this.newYield,
                    this.newRecipe.description || this.newProduct.name,
                    this.newVariant
                ],
                imports = Object.keys(this.newImports)
                    .filter((o) => this.newImports[o])
                    .join(",");

            this.$inertia.get(
                `/${parts.join("/")}?belt_speed=${this.newBeltSpeed}&factory=${
                    this.factory ? this.factory.id : ""
                }&imports=${imports}`
            );
        },

        async fetchNewYield() {
            if (this.yield < 1) return false;

            this.working = true;

            let parts = [
                    "newyield",
                    this.newProduct.name,
                    this.newYield,
                    this.newRecipe.description || this.newProduct.name,
                    this.newVariant
                ],
                raw = [],
                imports = Object.keys(this.newImports)
                    .filter((o) => this.newImports[o])
                    .join(",");

            for (let prop in this.rawMaterials) {
                if (!this.disabledRawMaterials[prop])
                    raw.push(`${prop}:${this.rawMaterials[prop]}`);
            }

            if (!raw.length) return false;

            this.$inertia.get(
                `/${parts.join("/")}?belt_speed=${
                    this.newBeltSpeed
                }&raw=${raw.join(",")}&factory=${
                    this.factory ? this.factory.id : ""
                }&imports=${imports}`
            );
        },

        saveMyFactory() {
            let name,
                imports = Object.keys(this.newImports)
                    .filter((o) => this.newImports[o])
                    .join(",");

            if (this.factory) {
                this.$inertia.patch(`/factories/${this.factory.id}`, {
                    name: this.factory.name,
                    ingredient_id: this.product.id,
                    recipe_id: this.recipe.id,
                    yield: this.yield,
                    imports
                });
            } else {
                name = prompt("Provide a name for your factory");
            }

            if (!name) return;

            this.$inertia.post("/factories", {
                name,
                ingredient_id: this.product.id,
                recipe_id: this.recipe.id,
                yield: this.yield,
                imports
            });
        },

        setNewSubFavorite(recipe) {
            if (recipe.product_id === this.recipe.product_id)
                this.$inertia.post(`/favorites/${recipe.id}`);
            else {
                this.$inertia.post(`/favorites/sub/${recipe.id}`);
                setTimeout(this.$forceUpdate, 1200);
            }
        },

        setNewFavorite() {
            this.$inertia.post(`/favorites/${this.recipe.id}`);
        },

        setDefaultRecipe() {
            this.recipes[this.newProduct.name].forEach((recipe) => {
                if (this.isFavorite(recipe)) {
                    this.setRecipe(recipe);
                }
            });
        },

        isFavorite(recipe) {
            return !!recipe.favorite;
        },

        setRecipe(recipe) {
            this.newRecipe = recipe;
            //this.newYield = 10;
            this.fetch();
        },

        reset() {
            this.$inertia.get("dashboard");
        },

        getFootprint(name) {
            return this.production.recipes[name].building_details[
                this.production.recipes[name].selected_variant
                ].footprint;
        },

        toggleProductionCheck(material) {
            if (this.productionChecks.hasOwnProperty(material))
                this.productionChecks[material] =
                    !this.productionChecks[material];
            else this.productionChecks[material] = true;
        },

        selectNewRecipe({ recipe }) {
            this.setRecipe(recipe);
        },

        selectNewSubRecipe({ recipe }) {
            this.setNewSubFavorite(recipe);
        },

        savePrefs() {
            store.setItem("diagrams", this.diagrams);
        },

        getOutputs(name) {
            let ret = {};

            Object.keys(this.production.recipes)
                .filter((product) => {
                    return !!this.production.recipes[product];
                })
                .filter((product) => {
                    return (
                        this.production.recipes[product].inputs[name]
                            ?.needed_qty > 0
                    );
                })
                .forEach((product) => {
                    ret[product] =
                        Math.round(
                            10000 *
                            this.production.recipes[product].inputs[name]
                                .needed_qty
                        ) / 10000;
                });

            return ret;
        },

        helpRawMaterials() {
            alert(
                "Constrained by something? Enter your actual available raw materials then click Update Yield. Click the green button next to the input to ignore that material for the recalculation."
            );
        },

        helpImport() {
            alert(
                "Choose whether to produce each intermediate product in this factory (default) or to import select products from elsewhere."
            );
        }
    }
};
</script>

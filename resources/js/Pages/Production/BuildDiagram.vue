<template>
    <div class='flex justify-end space-x-8'>
        <div class='w-48 text-left'>
            <ul>
                <li class='flex border-b border-gray-300'>
                    <span class='ml-2 font-semibold'>
                    Foundations
                    </span>
                    <span class='flex-1 text-right'>
                    {{ footprint.foundations }}
                    ( {{ footprint.length_foundations }}
                    x
                    {{ footprint.width_foundations }})
                    </span>
                </li>
                <li
                    class='flex border-b border-gray-300'
                >
                    <span class='ml-2 font-semibold'>Walls</span>
                    <span class='flex-1 text-right'>
                        {{ footprint.walls }}
                        ({{ footprint.height_walls }}
                        x
                        {{ 2 * (footprint.length_foundations + footprint.width_foundations) }})
                    </span>
                </li>
                <li class='flex border-b border-gray-300'>
                    <span class='ml-2 font-semibold'>Building Rows</span>
                    <span class='flex-1 text-right'>
                        {{ footprint.rows }}
                    </span>
                </li>
                <li class='flex border-b border-gray-300'>
                    <span class='ml-2 font-semibold'>
                        Buildings Per Row
                    </span>
                    <span class='flex-1 text-right'>
                        {{ footprint.buildings_per_row }}
                    </span>
                </li>
                <li class='flex border-b border-gray-300'>
                    <span class='ml-2 font-semibold'>
                        Belt Speed
                    </span>
                    <span class='flex-1 text-right'>
                        {{ footprint.belt_speed }}
                    </span>
                </li>
            </ul>
        </div>
        <div
            class='flex justify-center p-2'
        >
            <div style='box-sizing: content-box;'
                 :style="{
                    height: footprint.length_foundations * 2 +'rem',
                    width: footprint.width_foundations * 2 +'rem',
                }"
                 class='relative flex items-start justify-center bg-blue-300 text-xl shadow-lg'
            >
                <div
                    style='opacity: 0.3; box-sizing: content-box;'
                    class='absolute flex h-full w-full flex-wrap items-center justify-center'
                >
                    <template v-for='ii in Array(footprint.foundations)'>
                        <div class='border border-blue-500'
                             style='box-sizing: border-box;height: 2rem;width: 2rem;'></div>
                    </template>
                </div>
                <div style='padding: 2rem;'
                     class='absolute flex h-full w-full flex-wrap items-center justify-center'
                >
                    <div class='flex w-full items-center justify-center'
                         v-for='(ii,row) in Array(footprint.rows)'
                    >
                        <div v-for='(jj,col) in Array(footprint.buildings_per_row)'
                             :style="{
                                height: footprint.building_length /4 +'rem',
                                width: footprint.building_width /4 +'rem',
                            }"
                             :class="1 + col + row * footprint.buildings_per_row <= footprint.num_buildings
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
                            style='box-sizing: border-box;'
                            class='flex items-center justify-center rounded border bg-opacity-25 text-xs'
                        >
                            {{ footprint.monogram }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: 'build-diagram',
    props: {
        footprint: {
            type: Object,
            default() {
                return {
                    foundations: 0,
                    length_foundations: 0,
                    width_foundations: 0,
                    monogram: '',
                    buildings_per_row: 0,
                    rows: 0,
                    building_length: 0,
                    building_width: 0,
                    belt_speed: 0,
                    walls: 0,
                    height_walls: 0
                }
            }
        },
    },
};
</script>

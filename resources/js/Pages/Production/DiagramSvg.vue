<template>
    <svg
        class="block h-auto w-full"
        :viewBox="viewBox"
        preserveAspectRatio="xMidYMid meet"
        role="img"
        aria-label="Build diagram"
    >
        <!-- foundation slab -->
        <rect x="0" y="0" :width="canvasWidth" :height="canvasHeight" :fill="COLORS.slab" />

        <!-- foundation grid (8 m squares) -->
        <g :stroke="COLORS.grid" stroke-width="0.25" opacity="0.3">
            <line v-for="x in gridX" :key="`gx-${x}`" :x1="x" y1="0" :x2="x" :y2="canvasHeight" />
            <line v-for="y in gridY" :key="`gy-${y}`" x1="0" :y1="y" :x2="canvasWidth" :y2="y" />
        </g>

        <!-- building / blueprint tiles -->
        <g v-for="tile in tiles" :key="tile.key">
            <rect
                data-tile
                :x="tile.x"
                :y="tile.y"
                :width="footprint.building_width"
                :height="footprint.building_length"
                rx="1"
                :fill="COLORS.tile"
                fill-opacity="0.25"
                :stroke="COLORS.tileBorder"
                stroke-width="0.5"
            />
            <text
                :x="tile.x + footprint.building_width / 2"
                :y="tile.y + footprint.building_length / 2"
                text-anchor="middle"
                dominant-baseline="central"
                :font-size="monogramFontSize"
                :fill="COLORS.label"
            >
                {{ footprint.monogram }}
            </text>
        </g>

        <!-- dimension markers -->
        <g v-if="footprint.top_offset > 0" data-marker="top-offset">
            <line :x1="markerX" y1="0" :x2="markerX" :y2="footprint.top_offset" :stroke="COLORS.marker" stroke-width="0.25" />
            <text
                :x="markerX"
                :y="footprint.top_offset / 2"
                text-anchor="middle"
                dominant-baseline="central"
                :font-size="markerFontSize"
                :fill="COLORS.label"
            >
                {{ footprint.top_offset }} m
            </text>
        </g>
        <g v-if="footprint.bottom_offset > 0" data-marker="bottom-offset">
            <line
                :x1="markerX"
                :y1="canvasHeight - footprint.bottom_offset"
                :x2="markerX"
                :y2="canvasHeight"
                :stroke="COLORS.marker"
                stroke-width="0.25"
            />
            <text
                :x="markerX"
                :y="canvasHeight - footprint.bottom_offset / 2"
                text-anchor="middle"
                dominant-baseline="central"
                :font-size="markerFontSize"
                :fill="COLORS.label"
            >
                {{ footprint.bottom_offset }} m
            </text>
        </g>
        <g v-if="footprint.left_offset > 0" data-marker="left-offset">
            <line x1="0" :y1="canvasHeight / 2" :x2="footprint.left_offset" :y2="canvasHeight / 2" :stroke="COLORS.marker" stroke-width="0.25" />
            <text
                :x="footprint.left_offset / 2"
                :y="canvasHeight / 2"
                text-anchor="middle"
                dominant-baseline="central"
                :font-size="markerFontSize"
                :fill="COLORS.label"
            >
                {{ footprint.left_offset }} m
            </text>
        </g>
        <g v-if="footprint.left_offset > 0" data-marker="right-offset">
            <line
                :x1="canvasWidth - footprint.left_offset"
                :y1="canvasHeight / 2"
                :x2="canvasWidth"
                :y2="canvasHeight / 2"
                :stroke="COLORS.marker"
                stroke-width="0.25"
            />
            <text
                :x="canvasWidth - footprint.left_offset / 2"
                :y="canvasHeight / 2"
                text-anchor="middle"
                dominant-baseline="central"
                :font-size="markerFontSize"
                :fill="COLORS.label"
            >
                {{ footprint.left_offset }} m
            </text>
        </g>
        <g v-if="footprint.rows > 1 && footprint.row_spacing > 0" data-marker="row-spacing">
            <line
                :x1="markerX"
                :y1="rowSpacingTop"
                :x2="markerX"
                :y2="rowSpacingTop + footprint.row_spacing"
                :stroke="COLORS.marker"
                stroke-width="0.25"
            />
            <text
                :x="markerX"
                :y="rowSpacingTop + footprint.row_spacing / 2"
                text-anchor="middle"
                dominant-baseline="central"
                :font-size="markerFontSize"
                :fill="COLORS.label"
            >
                {{ footprint.row_spacing }} m
            </text>
        </g>
    </svg>
</template>
<script>
const FOUNDATION_M = 8;

// full diagram extent in meters — the default view and the modal's reset view (V113/V114)
export const diagramExtent = (footprint) => ({
    x: 0,
    y: 0,
    w: footprint.width_foundations * FOUNDATION_M,
    h: footprint.length_foundations * FOUNDATION_M,
});

// tailwind blue-300 / blue-500 / blue-800 — inline because SVG attrs, not classes
const COLORS = {
    slab: '#93c5fd',
    grid: '#3b82f6',
    tile: '#3b82f6',
    tileBorder: '#1e40af',
    marker: '#000000',
    label: '#000000',
};

export default {
    name: 'diagram-svg',
    props: {
        footprint: {
            type: Object,
            required: true,
        },
        // optional pan/zoom window {x, y, w, h} in meters — overrides the fit-all viewBox (V114)
        view: {
            type: Object,
            default: null,
        },
    },
    computed: {
        COLORS() {
            return COLORS;
        },
        canvasWidth() {
            return this.footprint.width_foundations * FOUNDATION_M;
        },
        canvasHeight() {
            return this.footprint.length_foundations * FOUNDATION_M;
        },
        viewBox() {
            const { x, y, w, h } = this.view ?? diagramExtent(this.footprint);

            return `${x} ${y} ${w} ${h}`;
        },
        gridX() {
            return Array.from({ length: Math.max(0, this.footprint.width_foundations - 1) }, (_, i) => (i + 1) * FOUNDATION_M);
        },
        gridY() {
            return Array.from({ length: Math.max(0, this.footprint.length_foundations - 1) }, (_, i) => (i + 1) * FOUNDATION_M);
        },
        rowPitch() {
            return this.footprint.building_length + this.footprint.row_spacing;
        },
        tiles() {
            const { rows, buildings_per_row, num_buildings, left_offset, top_offset, building_top_offset, building_width } =
                this.footprint;

            return Array.from({ length: rows * buildings_per_row }, (_, i) => ({
                key: i,
                x: left_offset + (i % buildings_per_row) * building_width,
                y: top_offset + building_top_offset + Math.floor(i / buildings_per_row) * this.rowPitch,
            })).slice(0, num_buildings);
        },
        rowSpacingTop() {
            return this.footprint.row_spacing_offset + this.footprint.building_top_offset;
        },
        markerX() {
            return this.canvasWidth * 0.4;
        },
        monogramFontSize() {
            return Math.min(this.footprint.building_width, this.footprint.building_length) / 3;
        },
        markerFontSize() {
            return Math.max(3, Math.min(6, this.canvasWidth / 28));
        },
    },
};
</script>

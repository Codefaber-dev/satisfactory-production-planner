<template>
    <svg
        class="block w-full"
        :viewBox="viewBox"
        preserveAspectRatio="xMidYMid meet"
        role="img"
        aria-label="Build diagram"
    >
        <defs>
            <!-- subtle flow arrows: half-size triangle at reduced opacity (V120) -->
            <marker
                v-for="line in manifoldLines"
                :key="`marker-${line.idx}`"
                :id="`${uid}-arrow-${line.idx}`"
                markerUnits="userSpaceOnUse"
                markerWidth="1.6"
                markerHeight="1.6"
                refX="1.3"
                refY="0.8"
                orient="auto"
            >
                <path d="M0,0 L1.6,0.8 L0,1.6 z" :fill="line.color" fill-opacity="0.55" />
            </marker>
        </defs>

        <!-- foundation slab -->
        <rect x="0" y="0" :width="canvasWidth" :height="canvasHeight" :fill="COLORS.slab" />

        <!-- foundation grid (8 m squares); vector-effect is per-element, never inherited (V122) -->
        <g :stroke="COLORS.grid" stroke-width="1" opacity="0.3">
            <line v-for="x in gridX" :key="`gx-${x}`" :x1="x" y1="0" :x2="x" :y2="canvasHeight" vector-effect="non-scaling-stroke" />
            <line v-for="y in gridY" :key="`gy-${y}`" x1="0" :y1="y" :x2="canvasWidth" :y2="y" vector-effect="non-scaling-stroke" />
        </g>

        <!-- belt manifolds: one splitter/merger line per material, hidden in blueprint
             mode — blueprints contain their own internals (V116). Trunks + stubs paint
             beneath the squares so a trunk crossing another slot stays readable -->
        <g v-for="line in manifoldLines" :key="`manifold-${line.idx}`" :data-manifold-line="line.material" :data-kind="line.kind">
            <line
                v-for="(seg, i) in line.trunkSegments"
                :key="`trunk-${i}`"
                data-trunk-segment
                :x1="seg.x1"
                :y1="seg.y1"
                :x2="seg.x2"
                :y2="seg.y2"
                :stroke="line.color"
                stroke-width="1.5"
                vector-effect="non-scaling-stroke"
                :stroke-dasharray="line.dash"
                :marker-end="`url(#${uid}-arrow-${line.idx})`"
            />
            <line
                v-for="(stub, i) in line.stubs"
                :key="`stub-${i}`"
                data-stub
                :x1="stub.x1"
                :y1="stub.y1"
                :x2="stub.x2"
                :y2="stub.y2"
                :stroke="line.color"
                stroke-width="1.5"
                vector-effect="non-scaling-stroke"
                :stroke-dasharray="line.dash"
                :marker-end="`url(#${uid}-arrow-${line.idx})`"
            />
            <g v-for="(sq, i) in line.squares" :key="`square-${i}`">
                <rect
                    data-manifold-square
                    :x="sq.x"
                    :y="sq.y"
                    :width="MANIFOLD_M"
                    :height="MANIFOLD_M"
                    rx="1"
                    :fill="line.color"
                    fill-opacity="0.25"
                    :stroke="line.color"
                    stroke-width="1.5"
                    vector-effect="non-scaling-stroke"
                />
                <text
                    :x="sq.cx"
                    :y="sq.y + MANIFOLD_M / 2"
                    text-anchor="middle"
                    dominant-baseline="central"
                    font-size="1.6"
                    :fill="COLORS.label"
                >
                    {{ line.monogram }}
                </text>
            </g>
            <text
                v-for="(label, i) in line.labels"
                :key="`label-${i}`"
                data-manifold-label
                :x="label.x"
                :y="label.y"
                text-anchor="end"
                dominant-baseline="central"
                font-size="2.5"
                :fill="line.color"
            >
                {{ line.material }}
            </text>
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
                stroke-width="1.5"
                vector-effect="non-scaling-stroke"
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
            <line
                :x1="markerX"
                y1="0"
                :x2="markerX"
                :y2="footprint.top_offset + footprint.building_top_offset"
                :stroke="COLORS.marker"
                stroke-width="1"
                vector-effect="non-scaling-stroke"
            />
            <text
                :x="markerX + 1"
                y="2"
                text-anchor="start"
                dominant-baseline="central"
                :font-size="markerFontSize"
                :fill="COLORS.label"
                :stroke="COLORS.slab"
                stroke-width="0.5"
                paint-order="stroke"
            >
                {{ footprint.top_offset }} m
            </text>
        </g>
        <g v-if="footprint.bottom_offset > 0" data-marker="bottom-offset">
            <line
                :x1="markerX"
                :y1="canvasHeight - footprint.bottom_offset + footprint.building_top_offset"
                :x2="markerX"
                :y2="canvasHeight"
                :stroke="COLORS.marker"
                stroke-width="1"
                vector-effect="non-scaling-stroke"
            />
            <text
                :x="markerX + 1"
                :y="canvasHeight - 2"
                text-anchor="start"
                dominant-baseline="central"
                :font-size="markerFontSize"
                :fill="COLORS.label"
                :stroke="COLORS.slab"
                stroke-width="0.5"
                paint-order="stroke"
            >
                {{ footprint.bottom_offset }} m
            </text>
        </g>
        <g v-if="footprint.left_offset > 0" data-marker="left-offset">
            <line x1="0" :y1="canvasHeight / 2" :x2="footprint.left_offset" :y2="canvasHeight / 2" :stroke="COLORS.marker" stroke-width="1" vector-effect="non-scaling-stroke" />
            <text
                :x="footprint.left_offset / 2"
                :y="canvasHeight / 2 - 2"
                text-anchor="middle"
                dominant-baseline="central"
                :font-size="markerFontSize"
                :fill="COLORS.label"
                :stroke="COLORS.slab"
                stroke-width="0.5"
                paint-order="stroke"
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
                stroke-width="1"
                vector-effect="non-scaling-stroke"
            />
            <text
                :x="canvasWidth - footprint.left_offset / 2"
                :y="canvasHeight / 2 - 2"
                text-anchor="middle"
                dominant-baseline="central"
                :font-size="markerFontSize"
                :fill="COLORS.label"
                :stroke="COLORS.slab"
                stroke-width="0.5"
                paint-order="stroke"
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
                stroke-width="1"
                vector-effect="non-scaling-stroke"
            />
            <text
                :x="markerX + 1"
                :y="rowSpacingTop + footprint.row_spacing / 2"
                text-anchor="start"
                dominant-baseline="central"
                :font-size="markerFontSize"
                :fill="COLORS.label"
                :stroke="COLORS.slab"
                stroke-width="0.5"
                paint-order="stroke"
            >
                {{ footprint.row_spacing }} m
            </text>
        </g>
    </svg>
</template>
<script>
import { unref } from 'vue';
import { FOUNDATION_M, MANIFOLD_M as MANIFOLD_SIZE_M, contentExtent, manifoldLines } from '@/diagramLayout';

// per-material line coding: color + dasharray both indexed by the material's
// position in the diagram's ordered list — distinct combos within a diagram (V116).
// 600-series for the light inline slab; 400-series for the deep-blue blueprint
// slab — the dark 600s are unreadable there (V125, B65). Same index mapping.
const MANIFOLD_PALETTE = ['#d97706', '#059669', '#7c3aed', '#dc2626', '#0891b2', '#db2777', '#65a30d', '#ea580c'];
const MANIFOLD_PALETTE_BLUEPRINT = [
    '#fbbf24',
    '#34d399',
    '#a78bfa',
    '#f87171',
    '#22d3ee',
    '#f472b6',
    '#a3e635',
    '#fb923c',
];

// tailwind blue-300 / blue-500 / blue-800 — inline because SVG attrs, not classes
const COLORS_DEFAULT = {
    slab: '#93c5fd',
    grid: '#3b82f6',
    tile: '#3b82f6',
    tileBorder: '#1e40af',
    marker: '#000000',
    label: '#000000',
};

// engineering-blueprint variant for the modal: deep blue slab, light line work;
// manifold palette colors stay — functional coding, readable on blue (V120)
const COLORS_BLUEPRINT = {
    slab: '#1e3a8a',
    grid: '#bfdbfe',
    tile: '#e2e8f0',
    tileBorder: '#e2e8f0',
    marker: '#bfdbfe',
    label: '#e0f2fe',
};

export default {
    name: 'diagram-svg',
    // Set of liquid/gas ingredient names (provided by Show.vue, V117);
    // absent (standalone mounts) ⇒ no filtering
    inject: {
        liquidNames: {
            default: null,
        },
    },
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
        // {inputs: [names], outputs: [names]} — drives the manifold lines; absent →
        // one generic input + one generic output line (V116)
        materials: {
            type: Object,
            default: null,
        },
        // engineering-blueprint color set — used by the diagram modal (V120)
        blueprint: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            // marker ids must be unique per svg instance — many diagrams per page
            uid: `dsvg-${Math.random().toString(36).slice(2)}`,
        };
    },
    computed: {
        COLORS() {
            return this.blueprint ? COLORS_BLUEPRINT : COLORS_DEFAULT;
        },
        MANIFOLD_M() {
            return MANIFOLD_SIZE_M;
        },
        canvasWidth() {
            return this.footprint.width_foundations * FOUNDATION_M;
        },
        canvasHeight() {
            return this.footprint.length_foundations * FOUNDATION_M;
        },
        viewBox() {
            // default view = the content extent, so overflowing stagger bands and
            // long labels stay visible instead of clipping at the slab edge (V124)
            const { x, y, w, h } = this.view ?? contentExtent(this.footprint, this.manifoldLines);

            return `${x} ${y} ${w} ${h}`;
        },
        gridX() {
            return Array.from(
                { length: Math.max(0, this.footprint.width_foundations - 1) },
                (_, i) => (i + 1) * FOUNDATION_M
            );
        },
        gridY() {
            return Array.from(
                { length: Math.max(0, this.footprint.length_foundations - 1) },
                (_, i) => (i + 1) * FOUNDATION_M
            );
        },
        rowPitch() {
            return this.footprint.building_length + this.footprint.row_spacing;
        },
        tiles() {
            const {
                rows,
                buildings_per_row,
                num_buildings,
                left_offset,
                top_offset,
                building_top_offset,
                building_width,
            } = this.footprint;

            return Array.from({ length: rows * buildings_per_row }, (_, i) => ({
                key: i,
                x: left_offset + (i % buildings_per_row) * building_width,
                y: top_offset + building_top_offset + Math.floor(i / buildings_per_row) * this.rowPitch,
            })).slice(0, num_buildings);
        },
        rowSpacingTop() {
            return this.footprint.row_spacing_offset + this.footprint.building_top_offset;
        },
        manifoldLines() {
            // geometry from the shared layout module (V124c); the component only
            // assigns colors — palette per render mode (V125)
            const palette = this.blueprint ? MANIFOLD_PALETTE_BLUEPRINT : MANIFOLD_PALETTE;

            return manifoldLines(this.footprint, this.materials, unref(this.liquidNames)).map((line) => ({
                ...line,
                color: palette[line.idx % palette.length],
            }));
        },
        markerX() {
            return this.canvasWidth * 0.4;
        },
        monogramFontSize() {
            return Math.min(this.footprint.building_width, this.footprint.building_length) / 3;
        },
        markerFontSize() {
            return 2.5;
        },
    },
};
</script>

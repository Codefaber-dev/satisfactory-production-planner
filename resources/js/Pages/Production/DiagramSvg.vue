<template>
    <svg
        class="block h-auto w-full"
        :viewBox="viewBox"
        preserveAspectRatio="xMidYMid meet"
        role="img"
        aria-label="Build diagram"
    >
        <defs>
            <marker
                v-for="line in manifoldLines"
                :key="`marker-${line.idx}`"
                :id="`${uid}-arrow-${line.idx}`"
                markerUnits="userSpaceOnUse"
                markerWidth="3"
                markerHeight="3"
                refX="2.5"
                refY="1.5"
                orient="auto"
            >
                <path d="M0,0 L3,1.5 L0,3 z" :fill="line.color" />
            </marker>
        </defs>

        <!-- foundation slab -->
        <rect x="0" y="0" :width="canvasWidth" :height="canvasHeight" :fill="COLORS.slab" />

        <!-- foundation grid (8 m squares) -->
        <g :stroke="COLORS.grid" stroke-width="0.25" opacity="0.3">
            <line v-for="x in gridX" :key="`gx-${x}`" :x1="x" y1="0" :x2="x" :y2="canvasHeight" />
            <line v-for="y in gridY" :key="`gy-${y}`" x1="0" :y1="y" :x2="canvasWidth" :y2="y" />
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
                stroke-width="0.4"
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
                stroke-width="0.4"
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
                    stroke-width="0.5"
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

// Conveyor Splitter / Merger real footprint (satisfactory.wiki.gg), meters (V116)
const MANIFOLD_M = 4;
// clearance from buildings and between grouped squares (V116)
const MANIFOLD_GAP_M = 2;

// per-material line coding: color + dasharray both indexed by the material's
// position in the diagram's ordered list — distinct combos within a diagram (V116)
const MANIFOLD_PALETTE = ['#d97706', '#059669', '#7c3aed', '#dc2626', '#0891b2', '#db2777', '#65a30d', '#ea580c'];
const MANIFOLD_DASHES = ['2 1', '1 1', '3 1', '0.5 1', '3 1 1 1', '4 2'];

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
        // {inputs: [names], outputs: [names]} — drives the manifold lines; absent →
        // one generic input + one generic output line (V116)
        materials: {
            type: Object,
            default: null,
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
            return COLORS;
        },
        MANIFOLD_M() {
            return MANIFOLD_M;
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
        manifoldLines() {
            if (this.footprint.grouped) {
                return [];
            }

            const inputs = this.materials ? this.materials.inputs ?? [] : ['Input'];
            const outputs = this.materials ? this.materials.outputs ?? [] : ['Output'];

            return [
                ...inputs.map((material, slot) => ({ material, kind: 'input', slot, slots: inputs.length })),
                ...outputs.map((material, slot) => ({ material, kind: 'output', slot, slots: outputs.length })),
            ].map((line, idx) => this.manifoldLine(line, idx));
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
    methods: {
        // one manifold line = per-row band of squares (one per building at this line's
        // slot), edge-to-edge trunk segments between consecutive squares, and a stub
        // to each building. Geometry per V116: group of N squares centered on the
        // building's vertical axis, 2 m in-group gap (compressed when the group would
        // outgrow the building width), bands 2 m off the row edge
        manifoldLine(line, idx) {
            const fp = this.footprint;
            const size = MANIFOLD_M;
            const gap =
                line.slots > 1
                    ? Math.min(MANIFOLD_GAP_M, Math.max(0, (fp.building_width - line.slots * size) / (line.slots - 1)))
                    : 0;
            const span = line.slots * size + (line.slots - 1) * gap;

            const squares = [];
            const trunkSegments = [];
            const stubs = [];
            const labels = [];

            for (let row = 0; row < fp.rows; row++) {
                const count = Math.min(fp.buildings_per_row, fp.num_buildings - row * fp.buildings_per_row);

                if (count <= 0) {
                    break;
                }

                const rowTop = fp.top_offset + fp.building_top_offset + row * (fp.building_length + fp.row_spacing);
                const rowBottom = rowTop + fp.building_length;
                const y =
                    line.kind === 'input'
                        ? Math.max(0, rowTop - MANIFOLD_GAP_M - size)
                        : Math.min(this.canvasHeight - size, rowBottom + MANIFOLD_GAP_M);
                const midY = y + size / 2;
                const centers = [];

                for (let b = 0; b < count; b++) {
                    const buildingCx = fp.left_offset + b * fp.building_width + fp.building_width / 2;
                    const x = buildingCx - span / 2 + line.slot * (size + gap);
                    const cx = x + size / 2;

                    centers.push(cx);
                    squares.push({ x, y, cx });
                    stubs.push(
                        line.kind === 'input'
                            ? { x1: cx, y1: y + size, x2: cx, y2: rowTop }
                            : { x1: cx, y1: rowBottom, x2: cx, y2: y },
                    );
                }

                for (let b = 0; b < centers.length - 1; b++) {
                    trunkSegments.push(
                        line.kind === 'input'
                            ? { x1: centers[b] + size / 2, y1: midY, x2: centers[b + 1] - size / 2, y2: midY }
                            : { x1: centers[b + 1] - size / 2, y1: midY, x2: centers[b] + size / 2, y2: midY },
                    );
                }

                if (row === 0) {
                    labels.push({ x: centers[0] - size / 2 - 1, y: midY });
                }
            }

            return {
                ...line,
                idx,
                color: MANIFOLD_PALETTE[idx % MANIFOLD_PALETTE.length],
                dash: MANIFOLD_DASHES[idx % MANIFOLD_DASHES.length],
                monogram: line.kind === 'input' ? 'SP' : 'MG',
                squares,
                trunkSegments,
                stubs,
                labels,
            };
        },
    },
};
</script>

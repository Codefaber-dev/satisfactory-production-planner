<template>
    <!-- master plan diagram (V139): every step a node, material flows as edges.
         Rendered in place like DiagramModal so dark-scoped styling survives -->
    <div
        data-master-diagram
        class="fixed inset-0 z-50 flex items-center justify-center overscroll-contain bg-black bg-opacity-70 p-4"
        @click.self="$emit('close')"
    >
        <!-- engineering-drawing sheet chrome, blueprint colors (V128 via V139) -->
        <div
            class="flex h-full w-full flex-col border-2 border-blue-200/60 bg-blue-950 font-mono text-blue-100 shadow-xl"
            data-drawing-sheet
        >
            <div class="flex flex-wrap items-stretch justify-between border-b-2 border-blue-200/60">
                <div class="px-4 py-1.5">
                    <div class="text-[9px] uppercase tracking-[0.25em] text-blue-300">Title</div>
                    <h2 class="text-lg font-bold uppercase tracking-widest text-blue-50">Master Diagram</h2>
                </div>
                <div class="flex items-center gap-2 border-l border-blue-200/60 px-3 py-1.5">
                    <button
                        type="button"
                        aria-label="Zoom in"
                        class="border border-blue-200/60 p-1.5 hover:bg-blue-800"
                        data-zoom-in
                        @click="zoomBy(ZOOM_STEP)"
                    >
                        <MagnifyingGlassPlusIcon class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        aria-label="Zoom out"
                        class="border border-blue-200/60 p-1.5 hover:bg-blue-800"
                        data-zoom-out
                        @click="zoomBy(1 / ZOOM_STEP)"
                    >
                        <MagnifyingGlassMinusIcon class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        class="border border-blue-200/60 px-2 py-1 text-xs uppercase tracking-wider hover:bg-blue-800"
                        data-reset-view
                        @click="resetView"
                    >
                        Reset View
                    </button>
                    <button
                        type="button"
                        aria-label="Close master diagram"
                        class="border border-blue-200/60 p-1.5 hover:bg-blue-800"
                        @click="$emit('close')"
                    >
                        <XMarkIcon class="h-4 w-4" />
                    </button>
                </div>
            </div>
            <div class="flex min-h-0 flex-1">
                <!-- stage -->
                <div
                    ref="stage"
                    class="min-h-0 min-w-0 flex-1 cursor-grab touch-none select-none overflow-hidden"
                    @wheel.prevent="onWheel"
                    @pointerdown="onPointerDown"
                    @pointermove="onPointerMove"
                    @pointerup="onPointerUp"
                    @pointercancel="onPointerUp"
                >
                    <svg
                        class="h-full w-full"
                        :viewBox="viewBox"
                        preserveAspectRatio="xMidYMid meet"
                        role="img"
                        aria-label="Master diagram"
                    >
                        <defs>
                            <marker
                                v-for="(material, i) in layout.materials"
                                :key="`marker-${i}`"
                                :id="`${uid}-arrow-${i}`"
                                markerUnits="userSpaceOnUse"
                                markerWidth="4"
                                markerHeight="4"
                                refX="3.2"
                                refY="2"
                                orient="auto"
                            >
                                <path d="M0,0 L4,2 L0,4 z" :fill="materialColor(material)" fill-opacity="0.8" />
                            </marker>
                        </defs>

                        <!-- edges: one per material flow, legend-filtered (V139/V140) -->
                        <g v-for="(edge, i) in visibleEdges" :key="`edge-${i}`" :data-edge="edge.material">
                            <line
                                :x1="edge.x1"
                                :y1="edge.y1"
                                :x2="edge.x2"
                                :y2="edge.y2"
                                :stroke="materialColor(edge.material)"
                                stroke-width="1.5"
                                vector-effect="non-scaling-stroke"
                                :stroke-dasharray="materialDash(edge.material)"
                                :marker-end="`url(#${uid}-arrow-${materialIndex(edge.material)})`"
                            />
                            <circle :cx="edge.x1" :cy="edge.y1" r="1" :fill="materialColor(edge.material)" />
                            <text
                                data-edge-label
                                :x="(edge.x1 + edge.x2) / 2"
                                :y="(edge.y1 + edge.y2) / 2 - 1.5"
                                text-anchor="middle"
                                font-size="3"
                                :fill="materialColor(edge.material)"
                            >
                                {{ edge.material }} {{ qty(edge.qty) }}/min
                            </text>
                        </g>

                        <!-- boundary markers: finals right, imports left (V139) -->
                        <g
                            v-for="(final, i) in visibleFinals"
                            :key="`final-${i}`"
                            :data-final-marker="final.material"
                        >
                            <line
                                :x1="final.x"
                                :y1="final.y"
                                :x2="final.x + MARKER_M"
                                :y2="final.y"
                                :stroke="materialColor(final.material)"
                                stroke-width="1.5"
                                vector-effect="non-scaling-stroke"
                                :marker-end="`url(#${uid}-arrow-${materialIndex(final.material)})`"
                            />
                            <text
                                :x="final.x + MARKER_M + 1"
                                :y="final.y"
                                text-anchor="start"
                                dominant-baseline="central"
                                font-size="3"
                                :fill="materialColor(final.material)"
                            >
                                {{ final.kind === 'final' ? 'OUTPUT' : final.dest }} {{ final.material }}
                                {{ qty(final.qty) }}/min
                            </text>
                        </g>
                        <g
                            v-for="(imp, i) in visibleImports"
                            :key="`import-${i}`"
                            :data-import-marker="imp.material"
                        >
                            <line
                                :x1="imp.x - MARKER_M"
                                :y1="imp.y"
                                :x2="imp.x"
                                :y2="imp.y"
                                :stroke="materialColor(imp.material)"
                                stroke-width="1.5"
                                vector-effect="non-scaling-stroke"
                                :marker-end="`url(#${uid}-arrow-${materialIndex(imp.material)})`"
                            />
                            <text
                                :x="imp.x - MARKER_M - 1"
                                :y="imp.y"
                                text-anchor="end"
                                dominant-baseline="central"
                                font-size="3"
                                :fill="materialColor(imp.material)"
                            >
                                IMPORT {{ imp.material }} {{ qty(imp.qty) }}/min
                            </text>
                        </g>

                        <!-- step nodes: dashed bounds + hover highlight (V141), semantic
                             zoom black-box ⇄ footprint interior (V142) -->
                        <g
                            v-for="node in layout.nodes"
                            :key="node.id"
                            :data-node="node.id"
                            :data-hovered="hovered === node.id ? '' : undefined"
                            @mouseenter="hovered = node.id"
                            @mouseleave="hovered = null"
                        >
                            <template v-if="detailed && node.footprint">
                                <!-- zoomed in: the step's own buildings, nested (V142) -->
                                <diagram-svg
                                    data-node-detail
                                    :footprint="node.footprint"
                                    :materials="nodeManifoldMaterials(node)"
                                    nested
                                    blueprint
                                    :x="node.x"
                                    :y="node.y"
                                    :width="node.w"
                                    :height="node.h"
                                />
                                <text
                                    :x="node.x + node.w / 2"
                                    :y="node.y - 2"
                                    text-anchor="middle"
                                    font-size="3.5"
                                    fill="#e0f2fe"
                                >
                                    {{ node.id }} {{ node.name }}{{ node.numBuildings ? ` ×${node.numBuildings}` : '' }}
                                </text>
                            </template>
                            <template v-else>
                                <!-- zoomed out: opaque black box (V142) -->
                                <rect
                                    data-node-box
                                    :x="node.x"
                                    :y="node.y"
                                    :width="node.w"
                                    :height="node.h"
                                    fill="#172554"
                                />
                                <text
                                    :x="node.x + node.w / 2"
                                    :y="node.y + node.h / 2 - 6"
                                    text-anchor="middle"
                                    dominant-baseline="central"
                                    font-size="14"
                                    fill="#e0f2fe"
                                    fill-opacity="0.9"
                                >
                                    {{ node.monogram || node.id }}
                                </text>
                                <text
                                    :x="node.x + node.w / 2"
                                    :y="node.y + node.h / 2 + 8"
                                    text-anchor="middle"
                                    dominant-baseline="central"
                                    font-size="4.5"
                                    fill="#e0f2fe"
                                >
                                    {{ node.id }} {{ node.name }}{{ node.numBuildings ? ` ×${node.numBuildings}` : '' }}
                                </text>
                            </template>
                            <!-- faint dashed bounds, hover-lifted (V141a/b) -->
                            <rect
                                data-node-bounds
                                :x="node.x"
                                :y="node.y"
                                :width="node.w"
                                :height="node.h"
                                fill="#bfdbfe"
                                :fill-opacity="hovered === node.id ? 0.08 : 0"
                                stroke="#bfdbfe"
                                :stroke-opacity="hovered === node.id ? 0.9 : 0.35"
                                stroke-width="1"
                                stroke-dasharray="3 2"
                                vector-effect="non-scaling-stroke"
                                pointer-events="all"
                            />
                            <!-- dedicated open affordance — the only click target, so
                                 pan/zoom drags can't misfire navigation (V141c) -->
                            <g
                                v-if="registryEntry(node.id)"
                                data-open-step
                                class="cursor-pointer"
                                role="button"
                                :aria-label="`Open ${node.name} build diagram`"
                                @click.stop="openStep(node)"
                                @pointerdown.stop
                            >
                                <rect
                                    :x="node.x + node.w - 9"
                                    :y="node.y + 1"
                                    width="8"
                                    height="8"
                                    rx="1"
                                    fill="#1e3a8a"
                                    stroke="#bfdbfe"
                                    stroke-width="1"
                                    vector-effect="non-scaling-stroke"
                                />
                                <text
                                    :x="node.x + node.w - 5"
                                    :y="node.y + 5"
                                    text-anchor="middle"
                                    dominant-baseline="central"
                                    font-size="5"
                                    fill="#e0f2fe"
                                >
                                    ⤢
                                </text>
                            </g>
                        </g>
                    </svg>
                </div>
                <!-- legend: per-material edge toggles, modal-local state (V140) -->
                <div
                    class="flex w-56 shrink-0 flex-col border-l border-blue-200/60 text-left text-xs"
                    data-master-legend
                >
                    <div class="px-2 py-1.5 text-[9px] uppercase tracking-[0.25em] text-blue-300">Materials</div>
                    <div class="flex gap-1 px-2 pb-1">
                        <button
                            type="button"
                            class="border border-blue-200/60 px-2 py-0.5 uppercase tracking-wider hover:bg-blue-800"
                            data-legend-show-all
                            @click="hiddenMaterials = []"
                        >
                            Show all
                        </button>
                        <button
                            type="button"
                            class="border border-blue-200/60 px-2 py-0.5 uppercase tracking-wider hover:bg-blue-800"
                            data-legend-hide-all
                            @click="hiddenMaterials = [...layout.materials]"
                        >
                            Hide all
                        </button>
                    </div>
                    <ul class="min-h-0 overflow-y-auto">
                        <li v-for="material in layout.materials" :key="material">
                            <label class="flex cursor-pointer items-center gap-2 px-2 py-1 hover:bg-blue-800">
                                <input
                                    type="checkbox"
                                    data-legend-toggle
                                    :value="material"
                                    :checked="!hiddenMaterials.includes(material)"
                                    @change="toggleMaterial(material)"
                                />
                                <span
                                    class="inline-block h-2 w-4 shrink-0"
                                    data-legend-swatch
                                    :style="{ backgroundColor: materialColor(material) }"
                                />
                                <span class="truncate">{{ material }}</span>
                            </label>
                        </li>
                    </ul>
                </div>
                <!-- cross-nav panel, same registry jump list as step modals (V141d/V130) -->
                <div class="flex shrink-0 flex-col border-l border-blue-200/60 text-left" data-modal-nav>
                    <button
                        type="button"
                        class="px-2 py-1.5 text-[9px] uppercase tracking-[0.25em] text-blue-300 hover:bg-blue-800"
                        :aria-label="navOpen ? 'Collapse step navigation' : 'Expand step navigation'"
                        data-modal-nav-toggle
                        @click="navOpen = !navOpen"
                    >
                        {{ navOpen ? 'Steps ▸' : '◂' }}
                    </button>
                    <ul v-if="navOpen" class="min-h-0 w-48 overflow-y-auto text-xs">
                        <li v-for="entry in navEntries" :key="entry.identifier">
                            <button
                                type="button"
                                class="w-full truncate px-2 py-1 text-left hover:bg-blue-800"
                                data-modal-nav-item
                                @click="navigateTo(entry)"
                            >
                                {{ entry.identifier }} {{ entry.name }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { MagnifyingGlassMinusIcon, MagnifyingGlassPlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { MANIFOLD_DASHES } from '@/diagramLayout';
import { diagramRegistry } from '@/diagramRegistry';
import { MARKER_M, NODE_W, masterLayout } from '@/masterDiagram';
import DiagramSvg from '@/Pages/Production/DiagramSvg';

// same fit-relative zoom window as the step modal (V114)
const MIN_ZOOM = 0.25;
const MAX_ZOOM = 8;
const ZOOM_STEP = 1.2;

// semantic-zoom switch (V142): node interiors render once a node is at least
// this wide on screen; below it the node paints as an opaque black box
export const DETAIL_THRESHOLD_PX = 200;

// blueprint-mode manifold palette values (V125) — master edges reuse them so
// materials read the same on the deep-blue sheet
const EDGE_PALETTE = ['#fbbf24', '#34d399', '#a78bfa', '#f87171', '#22d3ee', '#f472b6', '#a3e635', '#fb923c'];

export default {
    name: 'master-diagram',
    components: { DiagramSvg, MagnifyingGlassMinusIcon, MagnifyingGlassPlusIcon, XMarkIcon },
    props: {
        // ProductionSteps' computed shapes, passed straight through (V139)
        rawSteps: {
            type: Object,
            default: () => ({}),
        },
        levels: {
            type: Array,
            default: () => [],
        },
        recycleSteps: {
            type: Array,
            default: () => [],
        },
        byproductsUsed: {
            type: Object,
            default: () => ({}),
        },
        overviews: {
            type: Object,
            default: () => ({}),
        },
        buildingMultiples: {
            type: Object,
            default: () => ({}),
        },
        designerMk: {
            type: String,
            default: 'mk1',
        },
        appliedEven: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['close'],
    data() {
        return {
            view: null,
            pointers: {},
            panStart: null,
            navOpen: true,
            hovered: null,
            // legend-hidden materials — modal-local, reopen resets to all ON (V140)
            hiddenMaterials: [],
            uid: `mdsvg-${Math.random().toString(36).slice(2)}`,
        };
    },
    computed: {
        ZOOM_STEP() {
            return ZOOM_STEP;
        },
        MARKER_M() {
            return MARKER_M;
        },
        layout() {
            return masterLayout({
                rawSteps: this.rawSteps,
                levels: this.levels,
                recycleSteps: this.recycleSteps,
                byproductsUsed: this.byproductsUsed,
                overviews: this.overviews,
                buildingMultiples: this.buildingMultiples,
                designerMk: this.designerMk,
                appliedEven: this.appliedEven,
            });
        },
        viewBox() {
            const { x, y, w, h } = this.view ?? this.layout.extent;

            return `${x} ${y} ${w} ${h}`;
        },
        visibleEdges() {
            return this.layout.edges.filter((edge) => !this.hiddenMaterials.includes(edge.material));
        },
        visibleFinals() {
            return this.layout.finals.filter((final) => !this.hiddenMaterials.includes(final.material));
        },
        visibleImports() {
            return this.layout.imports.filter((imp) => !this.hiddenMaterials.includes(imp.material));
        },
        // V142: on-screen node width vs the detail threshold; jsdom (zero stage
        // box) falls back to the extent width, i.e. NODE_W px at fit view
        detailed() {
            if (!this.view?.w) {
                return false;
            }

            const pixels = this.$refs.stage?.clientWidth || this.layout.extent.w;

            return (pixels * NODE_W) / this.view.w >= DETAIL_THRESHOLD_PX;
        },
        navEntries() {
            return [...diagramRegistry].sort((a, b) =>
                a.identifier.localeCompare(b.identifier, undefined, { numeric: true })
            );
        },
    },
    created() {
        this.view = this.layout.extent;
    },
    mounted() {
        window.addEventListener('keydown', this.onKeydown);
        this.view = this.fitView();
    },
    beforeUnmount() {
        window.removeEventListener('keydown', this.onKeydown);
    },
    methods: {
        qty(value) {
            return +(value ?? 0).toFixed(2);
        },
        materialIndex(material) {
            return this.layout.materials.indexOf(material);
        },
        materialColor(material) {
            return EDGE_PALETTE[this.materialIndex(material) % EDGE_PALETTE.length];
        },
        materialDash(material) {
            return MANIFOLD_DASHES[this.materialIndex(material) % MANIFOLD_DASHES.length];
        },
        toggleMaterial(material) {
            this.hiddenMaterials = this.hiddenMaterials.includes(material)
                ? this.hiddenMaterials.filter((name) => name !== material)
                : [...this.hiddenMaterials, material];
        },
        // manifold lines for a nested node interior — the node's own step materials
        nodeManifoldMaterials(node) {
            const prod = node.prod;

            if (!prod) {
                return { inputs: (node.sinkInputs || []).map((input) => input.item), outputs: [] };
            }

            return {
                inputs: Object.keys(prod.ingredients || {}),
                outputs: [node.name, ...Object.keys(prod.byproducts || {})],
            };
        },
        registryEntry(identifier) {
            return diagramRegistry.find((entry) => entry.identifier === identifier) ?? null;
        },
        // dedicated affordance click (V141c): open the step's own diagram modal
        openStep(node) {
            const entry = this.registryEntry(node.id);

            if (entry) {
                entry.open();
                this.$emit('close');
            }
        },
        navigateTo(entry) {
            entry.open();
            this.$emit('close');
        },
        onKeydown(event) {
            if (event.key === 'Escape') {
                this.$emit('close');
            }
        },
        fitView() {
            const full = this.layout.extent;
            const stage = this.$refs.stage;
            const ratio =
                stage?.clientWidth && stage?.clientHeight ? stage.clientHeight / stage.clientWidth : full.h / full.w;
            const w = Math.max(full.w, full.h / ratio);
            const h = w * ratio;

            return {
                x: full.x - (w - full.w) / 2,
                y: full.y - (h - full.h) / 2,
                w,
                h,
            };
        },
        resetView() {
            this.view = this.fitView();
        },
        unitsPerPixel() {
            const pixels = this.$refs.stage?.clientWidth || this.view.w;

            return this.view.w / pixels;
        },
        zoomBy(factor) {
            const full = this.layout.extent;
            const width = Math.min(full.w / MIN_ZOOM, Math.max(full.w / MAX_ZOOM, this.view.w / factor));
            const height = width * (this.view.h / this.view.w);

            this.view = {
                x: this.view.x + (this.view.w - width) / 2,
                y: this.view.y + (this.view.h - height) / 2,
                w: width,
                h: height,
            };
        },
        onWheel(event) {
            this.zoomBy(event.deltaY < 0 ? ZOOM_STEP : 1 / ZOOM_STEP);
        },
        pinchDistance() {
            const [a, b] = Object.values(this.pointers);

            return Math.hypot(a.x - b.x, a.y - b.y) || 1;
        },
        onPointerDown(event) {
            event.currentTarget.setPointerCapture?.(event.pointerId);
            this.pointers = { ...this.pointers, [event.pointerId]: { x: event.clientX, y: event.clientY } };

            if (Object.keys(this.pointers).length === 1) {
                this.panStart = { x: event.clientX, y: event.clientY, view: this.view };
            } else {
                this.panStart = null;
            }
        },
        onPointerMove(event) {
            if (!(event.pointerId in this.pointers)) {
                return;
            }

            const touching = Object.keys(this.pointers).length;

            if (touching === 2) {
                const before = this.pinchDistance();
                this.pointers = { ...this.pointers, [event.pointerId]: { x: event.clientX, y: event.clientY } };
                this.zoomBy(this.pinchDistance() / before);

                return;
            }

            this.pointers = { ...this.pointers, [event.pointerId]: { x: event.clientX, y: event.clientY } };

            if (this.panStart) {
                const scale = this.unitsPerPixel();

                this.view = {
                    ...this.panStart.view,
                    x: this.panStart.view.x - (event.clientX - this.panStart.x) * scale,
                    y: this.panStart.view.y - (event.clientY - this.panStart.y) * scale,
                };
            }
        },
        onPointerUp(event) {
            const { [event.pointerId]: gone, ...rest } = this.pointers;
            this.pointers = rest;
            this.panStart = null;
        },
    },
};
</script>

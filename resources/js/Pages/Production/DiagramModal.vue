<template>
    <!-- rendered in place (no teleport): the dark variant is scoped to AppLayout's
         .dark root, and a body teleport would escape it — dark: classes would never
         fire inside the modal (V115) -->
    <div
        data-diagram-modal
        class="fixed inset-0 z-50 flex items-center justify-center overscroll-contain bg-black bg-opacity-70 p-4"
        @click.self="$emit('close')"
    >
        <!-- engineering-drawing sheet in blueprint colors: title-block layout,
             deep-blue surface, light-blue hairlines, mono — independent of app
             theme (V128 layout, V120 palette) -->
        <div
            class="flex h-full w-full max-w-6xl flex-col border-2 border-blue-200/60 bg-blue-950 font-mono text-blue-100 shadow-xl"
            data-drawing-sheet
        >
            <!-- header strip: STEP + TITLE compartments + outline controls -->
            <div class="flex flex-wrap items-stretch justify-between border-b-2 border-blue-200/60">
                <div class="flex items-stretch">
                    <div v-if="identifier" class="border-r border-blue-200/60 px-4 py-1.5" data-step-cell>
                        <div class="text-[9px] uppercase tracking-[0.25em] text-blue-300">Step</div>
                        <div class="text-lg font-bold tracking-widest text-blue-50">{{ identifier }}</div>
                    </div>
                    <div class="px-4 py-1.5">
                        <div class="text-[9px] uppercase tracking-[0.25em] text-blue-300">Title</div>
                        <h2 class="text-lg font-bold uppercase tracking-widest text-blue-50">{{ heading }}</h2>
                    </div>
                    <div
                        v-if="details?.recipe_name"
                        class="border-l border-blue-200/60 px-4 py-1.5"
                        data-recipe-cell
                    >
                        <div class="text-[9px] uppercase tracking-[0.25em] text-blue-300">Recipe</div>
                        <div class="text-lg tracking-widest text-blue-50">{{ details.recipe_name }}</div>
                    </div>
                    <!-- BUILT check: header compartment right of RECIPE (V144a rev) -->
                    <div v-if="checkKey" class="border-l border-blue-200/60 px-4 py-1.5" data-built-cell>
                        <div class="text-[9px] uppercase tracking-[0.25em] text-blue-300">Built</div>
                        <label class="flex cursor-pointer items-center gap-2">
                            <input
                                type="checkbox"
                                class="h-5 w-5 cursor-pointer"
                                data-built-checkbox
                                :checked="isChecked(checkKey)"
                                @change="toggleCheck(checkKey)"
                            />
                            <span class="text-lg">{{ isChecked(checkKey) ? '✅' : '—' }}</span>
                        </label>
                    </div>
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
                        aria-label="Close diagram"
                        class="border border-blue-200/60 p-1.5 hover:bg-blue-800"
                        @click="$emit('close')"
                    >
                        <XMarkIcon class="h-4 w-4" />
                    </button>
                </div>
            </div>
            <div class="flex min-h-0 flex-1">
                <div class="flex min-h-0 min-w-0 flex-1 flex-col">
                    <!-- stage: blueprint palette (V120/V125) -->
                    <div
                        ref="stage"
                        class="min-h-0 flex-1 cursor-grab touch-none select-none overflow-hidden"
                        @wheel.prevent="onWheel"
                        @pointerdown="onPointerDown"
                        @pointermove="onPointerMove"
                        @pointerup="onPointerUp"
                        @pointercancel="onPointerUp"
                    >
                        <diagram-svg
                            :footprint="footprint"
                            :materials="materials"
                            :view="view"
                            blueprint
                            class="h-full"
                        />
                    </div>
                    <!-- title block: bordered compartments, left-aligned, single-line (T174/V128) -->
                    <div
                        class="max-h-80 shrink-0 overflow-y-auto whitespace-nowrap border-t-2 border-blue-200/60 text-left text-sm"
                    >
                        <div class="flex flex-col lg:flex-row">
                            <div class="lg:border-r lg:border-blue-200/40">
                                <footprint-summary :footprint="footprint" />
                                <div class="flex">
                                    <div class="border-b border-r border-blue-200/40 px-2 py-1" data-scale-cell>
                                        <div class="text-[9px] uppercase tracking-widest text-blue-300">Scale</div>
                                        <div>1 m = {{ scalePx }} px</div>
                                    </div>
                                    <div class="border-b border-r border-blue-200/40 px-2 py-1">
                                        <div class="text-[9px] uppercase tracking-widest text-blue-300">Units</div>
                                        <div>Dimensions in meters</div>
                                    </div>
                                    <div
                                        v-if="details?.max_clock_speed"
                                        class="border-b border-r border-blue-200/40 px-2 py-1"
                                    >
                                        <div class="text-[9px] uppercase tracking-widest text-blue-300">
                                            Max Clock
                                        </div>
                                        <select
                                            class="border-0 bg-transparent p-0 font-mono text-sm text-blue-100 focus:ring-0"
                                            data-max-clock-select
                                            :value="details.max_clock_speed"
                                            @change="$emit('update:maxClock', Number($event.target.value))"
                                        >
                                            <option class="bg-blue-950" :value="100">100%</option>
                                            <option class="bg-blue-950" :value="150">150%</option>
                                            <option class="bg-blue-950" :value="200">200%</option>
                                            <option class="bg-blue-950" :value="250">250%</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- full step details: outputs/inputs/machines/build cost (V126) -->
                            <diagram-step-details
                                v-if="details"
                                :details="details"
                                :identifier="identifier"
                                class="flex-1"
                                @navigate="navigateTo"
                            />
                        </div>
                    </div>
                </div>
                <!-- cross-nav panel: jump to any other step's modal (V130) -->
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
                        <!-- reciprocal master-diagram jump (V141d): opener injected by
                             ProductionSteps; standalone mounts have none ⇒ no entry -->
                        <li v-if="openMasterDiagram">
                            <button
                                type="button"
                                class="w-full truncate px-2 py-1 text-left font-bold hover:bg-blue-800"
                                data-modal-nav-master
                                @click="goToMasterDiagram"
                            >
                                🗺 Full Production Diagram
                            </button>
                        </li>
                        <li v-for="entry in navEntries" :key="entry.identifier">
                            <button
                                type="button"
                                class="w-full truncate px-2 py-1 text-left"
                                :class="
                                    entry.identifier === identifier
                                        ? 'bg-blue-200/20 font-bold text-blue-50'
                                        : 'hover:bg-blue-800'
                                "
                                :disabled="entry.identifier === identifier"
                                :data-current="entry.identifier === identifier ? '' : undefined"
                                data-modal-nav-item
                                @click="navigateTo(entry)"
                            >
                                <!-- ✅ iff truthy store value at the entry's exact check key (V146) -->
                                {{ isChecked(entry.checkKey) ? '✅ ' : '' }}{{ entry.identifier }} {{ entry.name }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { unref } from 'vue';
import { MagnifyingGlassMinusIcon, MagnifyingGlassPlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { contentExtent, manifoldLines } from '@/diagramLayout';
import { diagramRegistry } from '@/diagramRegistry';
import { isChecked, toggleCheck } from '@/stepChecks';
import DiagramSvg from '@/Pages/Production/DiagramSvg';
import DiagramStepDetails from '@/Pages/Production/DiagramStepDetails.vue';
import FootprintSummary from './FootprintSummary.vue';

// zoom relative to the fit-all view: 0.25× (4× wider window) … 8× (⅛ window) (V114)
const MIN_ZOOM = 0.25;
const MAX_ZOOM = 8;
const ZOOM_STEP = 1.2;

export default {
    name: 'diagram-modal',
    components: {
        FootprintSummary,
        DiagramStepDetails,
        DiagramSvg,
        MagnifyingGlassMinusIcon,
        MagnifyingGlassPlusIcon,
        XMarkIcon,
    },
    // same liquid-name set DiagramSvg filters manifolds on (V117) — the modal's
    // fit/zoom extent must match what actually renders (V124)
    inject: {
        liquidNames: {
            default: null,
        },
        // opens the plan-wide master diagram (provided by ProductionSteps, V141d)
        openMasterDiagram: {
            default: null,
        },
    },
    props: {
        footprint: {
            type: Object,
            required: true,
        },
        title: {
            type: String,
            default: '',
        },
        materials: {
            type: Object,
            default: null,
        },
        // full step details payload for the side panel (V126); null ⇒ no section
        details: {
            type: Object,
            default: null,
        },
        // step identifier (e.g. '2.B') — header STEP cell + nav highlight (V130)
        identifier: {
            type: String,
            default: '',
        },
        // step-check store key — BUILT cell binding (V144); '' ⇒ cell hidden
        checkKey: {
            type: String,
            default: '',
        },
    },
    emits: ['close', 'update:maxClock'],
    data() {
        return {
            view: null,
            pointers: {},
            panStart: null,
            navOpen: true,
        };
    },
    computed: {
        ZOOM_STEP() {
            return ZOOM_STEP;
        },
        heading() {
            return this.title || `${this.footprint.monogram} Build Diagram`;
        },
        // every registered diagram, identifier-sorted — the modal jump list (V130)
        navEntries() {
            return [...diagramRegistry].sort((a, b) =>
                a.identifier.localeCompare(b.identifier, undefined, { numeric: true })
            );
        },
        // live scale readout for the title block (V128): screen pixels per meter
        // at the current zoom — recomputes with every view change; jsdom (zero
        // stage box) falls back to the full extent, i.e. 1:1 at fit view
        scalePx() {
            if (!this.view) {
                return 1;
            }

            const pixels = this.$refs.stage?.clientWidth || this.fullExtent.w;

            return +(pixels / this.view.w).toFixed(1);
        },
        // content extent (slab + manifold bands + labels, V124) — the fit/reset
        // view and zoom clamps all derive from this, matching DiagramSvg's render
        fullExtent() {
            return contentExtent(
                this.footprint,
                manifoldLines(this.footprint, this.materials, unref(this.liquidNames))
            );
        },
    },
    created() {
        this.view = this.fullExtent;
    },
    mounted() {
        window.addEventListener('keydown', this.onKeydown);
        // stage box is only measurable once mounted — refit so the view window
        // matches the stage aspect and the svg fills the modal (V121)
        this.view = this.fitView();
    },
    beforeUnmount() {
        window.removeEventListener('keydown', this.onKeydown);
    },
    methods: {
        isChecked,
        toggleCheck,

        onKeydown(event) {
            if (event.key === 'Escape') {
                this.$emit('close');
            }
        },
        // smallest stage-aspect window containing the whole diagram, centered —
        // the svg fills the stage at every zoom, no letterboxed dead space (V121);
        // unmeasurable stage (jsdom, zero box) falls back to the diagram's aspect
        fitView() {
            const full = this.fullExtent;
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
        // jump to the plan-wide master diagram: open it, close this modal (V141d)
        goToMasterDiagram() {
            this.openMasterDiagram();
            this.$emit('close');
        },
        // jump to another step's modal: open the target, close this one (V130)
        navigateTo(entry) {
            if (entry.identifier === this.identifier) {
                return;
            }

            entry.open();
            this.$emit('close');
        },
        // meters per screen pixel at the current zoom; jsdom (clientWidth 0) falls back to 1:1
        unitsPerPixel() {
            const pixels = this.$refs.stage?.clientWidth || this.view.w;

            return this.view.w / pixels;
        },
        zoomBy(factor) {
            const full = this.fullExtent;
            const width = Math.min(full.w / MIN_ZOOM, Math.max(full.w / MAX_ZOOM, this.view.w / factor));
            // preserve the CURRENT view aspect (stage-matched, V121), not the diagram's
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

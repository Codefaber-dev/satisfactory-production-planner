<template>
    <!-- rendered in place (no teleport): the dark variant is scoped to AppLayout's
         .dark root, and a body teleport would escape it — dark: classes would never
         fire inside the modal (V115) -->
    <div
        data-diagram-modal
        class="fixed inset-0 z-50 flex items-center justify-center overscroll-contain bg-black bg-opacity-70 p-4"
        @click.self="$emit('close')"
    >
        <!-- engineering-blueprint styling, independent of app theme (V120) -->
        <div
            class="flex h-full w-full max-w-6xl flex-col rounded border-2 border-blue-200/60 bg-blue-950 p-4 text-blue-100 shadow-xl"
            data-blueprint-panel
        >
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-blue-200/40 pb-2">
                <h2 class="font-mono text-xl font-bold uppercase tracking-widest text-blue-50">{{ heading }}</h2>
                <div class="flex items-center gap-2">
                    <button type="button" aria-label="Zoom in" class="btn btn-emerald" data-zoom-in @click="zoomBy(ZOOM_STEP)">
                        <MagnifyingGlassPlusIcon class="h-5 w-5" />
                    </button>
                    <button
                        type="button"
                        aria-label="Zoom out"
                        class="btn btn-emerald"
                        data-zoom-out
                        @click="zoomBy(1 / ZOOM_STEP)"
                    >
                        <MagnifyingGlassMinusIcon class="h-5 w-5" />
                    </button>
                    <button type="button" class="btn btn-emerald" data-reset-view @click="resetView">Reset View</button>
                    <button
                        type="button"
                        aria-label="Close diagram"
                        class="rounded p-1 hover:bg-blue-800"
                        @click="$emit('close')"
                    >
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
            </div>
            <div class="flex min-h-0 flex-1 flex-col gap-4 pt-2 font-mono text-sm lg:flex-row">
                <div class="shrink-0 overflow-y-auto">
                    <footprint-summary :footprint="footprint" />
                    <!-- full step details: outputs/inputs/machines/build cost (V126) -->
                    <diagram-step-details v-if="details" :details="details" />
                </div>
                <div
                    ref="stage"
                    class="min-h-0 flex-1 cursor-grab touch-none select-none overflow-hidden"
                    @wheel.prevent="onWheel"
                    @pointerdown="onPointerDown"
                    @pointermove="onPointerMove"
                    @pointerup="onPointerUp"
                    @pointercancel="onPointerUp"
                >
                    <diagram-svg :footprint="footprint" :materials="materials" :view="view" blueprint class="h-full" />
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { unref } from 'vue';
import { MagnifyingGlassMinusIcon, MagnifyingGlassPlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { contentExtent, manifoldLines } from '@/diagramLayout';
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
    },
    emits: ['close'],
    data() {
        return {
            view: null,
            pointers: {},
            panStart: null,
        };
    },
    computed: {
        ZOOM_STEP() {
            return ZOOM_STEP;
        },
        heading() {
            return this.title || `${this.footprint.monogram} Build Diagram`;
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

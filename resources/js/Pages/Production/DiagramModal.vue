<template>
    <!-- rendered in place (no teleport): the dark variant is scoped to AppLayout's
         .dark root, and a body teleport would escape it — dark: classes would never
         fire inside the modal (V115) -->
    <div
        data-diagram-modal
        class="fixed inset-0 z-50 flex items-center justify-center overscroll-contain bg-black bg-opacity-70 p-4"
        @click.self="$emit('close')"
    >
        <div
            class="flex h-full w-full max-w-6xl flex-col rounded border border-gray-300 bg-white p-4 text-gray-900 shadow-xl dark:border-sky-700 dark:bg-slate-900 dark:text-gray-100"
        >
            <div class="flex flex-wrap items-center justify-between gap-2 pb-2">
                <h2 class="text-xl font-bold">{{ heading }}</h2>
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
                        class="rounded p-1 hover:bg-gray-300 dark:hover:bg-slate-700"
                        @click="$emit('close')"
                    >
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
            </div>
            <div class="flex min-h-0 flex-1 flex-col gap-4 lg:flex-row">
                <div
                    ref="stage"
                    class="min-h-0 flex-1 cursor-grab touch-none select-none overflow-hidden"
                    @wheel.prevent="onWheel"
                    @pointerdown="onPointerDown"
                    @pointermove="onPointerMove"
                    @pointerup="onPointerUp"
                    @pointercancel="onPointerUp"
                >
                    <diagram-svg :footprint="footprint" :view="view" class="max-h-full" />
                </div>
                <div v-if="plan" class="w-full shrink-0 overflow-y-auto lg:w-80" data-summary-panel>
                    <building-summary
                        :production__building_summary="plan.summary"
                        :production__building_details="plan.details"
                        :production__total_power="plan.totalPower"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { unref } from 'vue';
import { MagnifyingGlassMinusIcon, MagnifyingGlassPlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import DiagramSvg, { diagramExtent } from '@/Pages/Production/DiagramSvg';
import BuildingSummary from '@/Pages/Production/BuildingSummary';

// zoom relative to the fit-all view: 0.25× (4× wider window) … 8× (⅛ window) (V114)
const MIN_ZOOM = 0.25;
const MAX_ZOOM = 8;
const ZOOM_STEP = 1.2;

export default {
    name: 'diagram-modal',
    components: { BuildingSummary, DiagramSvg, MagnifyingGlassMinusIcon, MagnifyingGlassPlusIcon, XMarkIcon },
    props: {
        footprint: {
            type: Object,
            required: true,
        },
        title: {
            type: String,
            default: '',
        },
    },
    emits: ['close'],
    // plan-level Building Summary data provided by Show.vue; absent in standalone
    // mounts → panel hidden (V115)
    inject: {
        planSummary: {
            default: null,
        },
    },
    data() {
        return {
            view: diagramExtent(this.footprint),
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
        plan() {
            return unref(this.planSummary);
        },
    },
    mounted() {
        window.addEventListener('keydown', this.onKeydown);
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
        resetView() {
            this.view = diagramExtent(this.footprint);
        },
        // meters per screen pixel at the current zoom; jsdom (clientWidth 0) falls back to 1:1
        unitsPerPixel() {
            const pixels = this.$refs.stage?.clientWidth || this.view.w;

            return this.view.w / pixels;
        },
        zoomBy(factor) {
            const full = diagramExtent(this.footprint);
            const width = Math.min(full.w / MIN_ZOOM, Math.max(full.w / MAX_ZOOM, this.view.w / factor));
            const height = width * (full.h / full.w);

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

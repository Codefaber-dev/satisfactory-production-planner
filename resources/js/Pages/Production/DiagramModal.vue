<template>
    <teleport to="body">
        <div
            data-diagram-modal
            class="fixed inset-0 z-50 flex items-center justify-center overscroll-contain bg-black bg-opacity-70 p-4"
            @click.self="$emit('close')"
        >
            <div class="flex h-full w-full max-w-6xl flex-col rounded bg-gray-100 p-4 shadow-xl">
                <div class="flex items-center justify-between pb-2">
                    <span class="text-lg font-semibold">{{ footprint.monogram }} Build Diagram</span>
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn btn-emerald" data-reset-view @click="resetView">
                            Reset View
                        </button>
                        <button
                            type="button"
                            aria-label="Close diagram"
                            class="rounded p-1 hover:bg-gray-300"
                            @click="$emit('close')"
                        >
                            <XMarkIcon class="h-6 w-6" />
                        </button>
                    </div>
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
                    <diagram-svg :footprint="footprint" :view="view" class="max-h-full" />
                </div>
            </div>
        </div>
    </teleport>
</template>
<script>
import { XMarkIcon } from '@heroicons/vue/24/outline';
import DiagramSvg, { diagramExtent } from '@/Pages/Production/DiagramSvg';

// zoom relative to the fit-all view: 0.25× (4× wider window) … 8× (⅛ window) (V114)
const MIN_ZOOM = 0.25;
const MAX_ZOOM = 8;

export default {
    name: 'diagram-modal',
    components: { DiagramSvg, XMarkIcon },
    props: {
        footprint: {
            type: Object,
            required: true,
        },
    },
    emits: ['close'],
    data() {
        return {
            view: diagramExtent(this.footprint),
            pointers: {},
            panStart: null,
        };
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
            this.zoomBy(event.deltaY < 0 ? 1.2 : 1 / 1.2);
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

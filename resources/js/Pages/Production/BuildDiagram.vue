<template>
    <div class="flex flex-col items-center gap-4 lg:flex-row lg:items-start lg:justify-end lg:gap-8">
        <footprint-summary :footprint="footprint" />
        <div class="w-full min-w-0 flex-1 p-2">
            <button
                type="button"
                aria-label="Expand diagram"
                title="Expand diagram — pan &amp; zoom"
                class="block w-full cursor-zoom-in"
                @click="modalOpen = true"
            >
                <diagram-svg :footprint="footprint" :materials="materials" class="h-auto max-h-96 shadow-lg" />
            </button>
            <diagram-modal
                v-if="modalOpen"
                :footprint="footprint"
                :materials="materials"
                :title="title"
                :details="details"
                :identifier="identifier"
                @close="modalOpen = false"
            />
        </div>
    </div>
</template>
<script>
import DiagramSvg from '@/Pages/Production/DiagramSvg';
import DiagramModal from '@/Pages/Production/DiagramModal';
import FootprintSummary from './FootprintSummary.vue';
import { registerDiagram } from '@/diagramRegistry';

export default {
    name: 'build-diagram',
    components: { FootprintSummary, DiagramSvg, DiagramModal },
    data() {
        return {
            modalOpen: false,
        };
    },
    mounted() {
        // modal cross-navigation (V130): only identifier-bearing diagrams join
        // the registry — standalone mounts (tests, previews) stay out
        if (this.identifier) {
            this.unregisterDiagram = registerDiagram({
                identifier: this.identifier,
                name: this.title,
                open: () => {
                    this.modalOpen = true;
                },
            });
        }
    },
    beforeUnmount() {
        this.unregisterDiagram?.();
    },
    props: {
        // step identifier (e.g. '2.B') for the modal cross-nav registry (V130)
        identifier: {
            type: String,
            default: '',
        },
        title: {
            type: String,
            default: '',
        },
        materials: {
            type: Object,
            default: null,
        },
        // step-details payload passed through to the modal side panel (V126)
        details: {
            type: Object,
            default: null,
        },
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
                    height_walls: 0,
                    row_spacing: 0,
                    row_spacing_offset: 0,
                    top_offset: 0,
                    bottom_offset: 0,
                    left_offset: 0,
                    building_top_offset: 0,
                };
            },
        },
    },
};
</script>

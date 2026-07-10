<template>
    <!-- modal-only diagram access (V134): compact trigger, no inline render -->
    <div class="flex justify-end py-2 mx-2">
        <button
            type="button"
            aria-label="Expand diagram"
            title="View build diagram — pan &amp; zoom"
            class="btn btn-emerald btn-sm"
            @click="modalOpen = true"
        >
            📐 View Diagram
        </button>
        <!-- V148: teleport the overlay out of the row subtree — a hidden host row
             (hideCompleted / filter / in-modal BUILT toggle) must not display:none
             the open modal. Safe: modal palette is fixed blueprint (V128), no
             dark: classes, so escaping the .dark scope costs nothing (B72). -->
        <teleport to="body">
            <diagram-modal
                v-if="modalOpen"
                :footprint="footprint"
                :materials="materials"
                :title="title"
                :details="details"
                :identifier="identifier"
                :check-key="checkKey"
                @close="modalOpen = false"
                @update:max-clock="$emit('update:maxClock', $event)"
            />
        </teleport>
    </div>
</template>
<script>
import DiagramModal from '@/Pages/Production/DiagramModal';
import { registerDiagram } from '@/diagramRegistry';

export default {
    name: 'build-diagram',
    emits: ['update:maxClock'],
    components: { DiagramModal },
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
                // step-check key so nav panels can render ✅ markers (V144/V146)
                checkKey: this.checkKey,
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
        // step-check store key for the modal's BUILT cell (V144); '' ⇒ no cell
        checkKey: {
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

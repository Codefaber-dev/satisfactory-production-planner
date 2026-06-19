<template>
    <div class="mt-2 flex flex-col items-end space-y-1">
        <!-- source-mode selector (V59): import | extract | convert | unpackage -->
        <div class="flex flex-wrap items-center justify-end gap-1">
            <span>Source</span>
            <button
                v-for="mode in availableModes"
                :key="mode"
                @click="setMode(mode)"
                :class="[currentMode === mode ? 'btn-gray' : 'btn-emerald']"
                class="btn-sm capitalize"
            >
                {{ mode }}
            </button>
        </div>

        <!-- extract controls (V60/V75): styled button groups; water hides purity/tier -->
        <div v-if="currentMode === 'extract'" class="flex flex-col items-end gap-1">
            <!-- extractor info line (V82): count/tier/clock/purity/power above the buttons -->
            <div v-if="extractorSummary" aria-label="extractor summary" class="text-sm font-semibold">
                {{ extractorSummary }}
            </div>
            <div v-if="showPurity" aria-label="purity" class="flex flex-wrap items-center justify-end gap-1">
                <span>Purity</span>
                <button
                    v-for="p in ['impure', 'normal', 'pure']"
                    :key="p"
                    @click="patch({ purity: p })"
                    :class="[(config.purity || 'normal') === p ? 'btn-gray' : 'btn-emerald']"
                    class="btn-sm capitalize"
                >
                    {{ p }}
                </button>
            </div>
            <div v-if="showTier" aria-label="miner tier" class="flex flex-wrap items-center justify-end gap-1">
                <span>Tier</span>
                <button
                    v-for="t in ['mk1', 'mk2', 'mk3']"
                    :key="t"
                    @click="patch({ miner: t })"
                    :class="[(config.miner || 'mk2') === t ? 'btn-gray' : 'btn-emerald']"
                    class="btn-sm"
                >
                    {{ tierLabel(t) }}
                </button>
            </div>
            <div aria-label="power shards" class="flex flex-wrap items-center justify-end gap-1">
                <span>Power Shards</span>
                <button
                    v-for="n in 4"
                    :key="n - 1"
                    @click="patch({ shards: n - 1 })"
                    :class="[(Number.parseInt(config.shards) || 0) === n - 1 ? 'btn-gray' : 'btn-emerald']"
                    class="btn-sm"
                >
                    {{ n - 1 }}
                </button>
            </div>
        </div>

        <!-- convert/unpackage (V79): the recipe is chosen via the main RecipePicker on
             the step row, NOT here. RawSourceControl is mode + extract params only. -->
        <span v-if="currentMode === 'convert'" class="italic text-gray-500">Pick a converter recipe above</span>
        <span v-else-if="currentMode === 'unpackage'" class="italic text-gray-500">
            Pick the Unpackage recipe above · packaged input imported by default
        </span>
    </div>
</template>
<script>
// solid ores worked by a miner — mirrors ExtractionCalc::MINER_ORES (§C / V74)
const MINER_ORES = [
    'Iron Ore',
    'Copper Ore',
    'Caterium Ore',
    'Coal',
    'Limestone',
    'Sulfur',
    'Raw Quartz',
    'Bauxite',
    'Uranium',
    'SAM',
];

export default {
    name: 'RawSourceControl',
    props: {
        name: {
            type: String,
            required: true,
        },
        // current raw_sources[name] config; {} → import
        config: {
            type: Object,
            default: () => ({}),
        },
        // recipes[name] — all recipes producing this raw (convert + unpackage options)
        recipeOptions: {
            type: Array,
            default: () => [],
        },
        // V82: this raw's ExtractorSummary row → { count, clock, power } for the info line
        extractor: {
            type: Object,
            default: null,
        },
    },

    computed: {
        currentMode() {
            return this.config.mode || 'import';
        },

        // extractor type mirrors backend ExtractionCalc::extractorType (V60/V74).
        // Biomass/organic raws are non-extractable ('none') — no miner default.
        extractorType() {
            const dedicated = { Water: 'water', 'Crude Oil': 'oil', 'Nitrogen Gas': 'well' }[this.name];
            if (dedicated) {
                return dedicated;
            }
            return MINER_ORES.includes(this.name) ? 'miner' : 'none';
        },

        isExtractable() {
            return this.extractorType !== 'none';
        },

        // only the miner has selectable purity + tier; water has neither (deep-water,
        // not node-bound); oil/well have purity but no tier (single building)
        showPurity() {
            return this.extractorType !== 'water';
        },

        showTier() {
            return this.extractorType === 'miner';
        },

        unpackageRecipes() {
            return this.recipeOptions.filter((r) => (r.description || '').startsWith('Unpackage'));
        },

        convertRecipes() {
            return this.recipeOptions.filter(
                (r) => !(r.description || '').startsWith('Unpackage') && (r.ingredients || []).length > 0
            );
        },

        // V82: `<N>x <extractor> @<clock>% [<Purity>] [<MW>]`, e.g.
        // `6x Miner (mk1) @100% [Pure] [24 MW]`. Null when no extractor row.
        extractorSummary() {
            if (!this.extractor || !this.extractor.count) {
                return null;
            }

            const label =
                this.extractorType === 'miner'
                    ? `Miner (${this.config.miner || 'mk2'})`
                    : { water: 'Water Extractor', oil: 'Oil Extractor', well: 'Resource Well Pressurizer' }[
                          this.extractorType
                      ];

            const clock = Math.round(this.extractor.clock ?? 100);
            const power = Math.round(this.extractor.power ?? 0);
            const cap = (s) => s.charAt(0).toUpperCase() + s.slice(1);
            const purity = this.showPurity ? ` [${cap(this.config.purity || 'normal')}]` : '';

            return `${this.extractor.count}x ${label} @${clock}%${purity} [${power} MW]`;
        },

        availableModes() {
            const modes = ['import'];
            // extract only for extractable raws — biomass/organic raws can't be mined (V74)
            if (this.isExtractable) {
                modes.push('extract');
            }
            if (this.convertRecipes.length) {
                modes.push('convert');
            }
            // unpackage only for fluid/gas raws — i.e. those with an Unpackage recipe (V63)
            if (this.unpackageRecipes.length) {
                modes.push('unpackage');
            }
            return modes;
        },
    },

    methods: {
        tierLabel(tier) {
            return { mk1: 'Mk.1', mk2: 'Mk.2', mk3: 'Mk.3' }[tier] ?? tier;
        },

        setMode(mode) {
            // V79: raw_sources stores {mode, purity, miner, shards} only — the
            // convert/unpackage recipe lives in `choices` (the main RecipePicker),
            // defaulted backend-side when unset. No recipe key emitted here.
            this.emitConfig({ ...this.config, mode });
        },

        patch(partial) {
            this.emitConfig({ ...this.config, mode: this.currentMode, ...partial });
        },

        emitConfig(config) {
            this.$emit('update', { name: this.name, config });
        },
    },
};
</script>

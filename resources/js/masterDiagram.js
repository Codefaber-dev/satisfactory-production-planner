// Master-diagram layout (V139): pure geometry for the whole-plan graph — one
// node per rendered step (Level-0 raws, production levels, terminal sink),
// one directed edge per material flow from the step `outputs` maps (qty > 0
// guaranteed upstream, V138). Finals and unproduced inputs become boundary
// markers so no edge dangles. Shared by MasterDiagram.vue (render) and tests.
import { DESIGNER_DIMS, groupedFootprint } from '@/blueprintFootprint';

// node box + spacing in diagram units ("meters" — same unit space DiagramSvg
// nests into). Constant per V142: layout never changes with zoom.
export const NODE_W = 96;
export const NODE_H = 64;
export const COL_GAP = 72;
export const ROW_GAP = 24;
export const MARGIN = 24;
// boundary marker stem length (imports left, finals right)
export const MARKER_M = 14;

const LETTERS = Array.from('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

// overview lookup key — frontend convention, V40 (`Step::getProductKey` shape)
const overviewKey = (prod, name) => {
    const recipe = prod?.recipe;

    return recipe ? `${recipe.product.name}|${recipe.description || recipe.product.name}` : name;
};

// footprint + building count for a step, resolved exactly like ProductionStep:
// server-emitted clock + variant, blueprint-grouped when a multiple is set
const stepDetail = (prod, name, { overviews, buildingMultiples, designerMk, appliedEven }) => {
    const entry = overviews?.[overviewKey(prod, name)];
    const overview = entry?.overviews?.[entry.clock];
    const detail = overview?.details?.[entry?.selected_variant_name];

    if (!detail?.footprint) {
        return { footprint: null, numBuildings: null, monogram: '' };
    }

    const groupSize = buildingMultiples?.[overview.building];
    const footprint = groupSize
        ? groupedFootprint(detail.footprint, groupSize, DESIGNER_DIMS[designerMk] ?? DESIGNER_DIMS.mk1, appliedEven)
        : detail.footprint;

    return { footprint, numBuildings: detail.num_buildings, monogram: detail.footprint.monogram };
};

// visible materials of a level, mirroring ProductionSteps.getStepIndex — a
// material renders when it has a recipe-bearing production and isn't imported
const visibleLevelMaterials = (level) =>
    Object.entries(level).filter(
        ([, material]) => material.production.some((prod) => prod.recipe) && !material.imported
    );

const stepProd = (material) => material?.production?.find((prod) => prod.recipe) ?? material?.production?.[0] ?? null;

// build every node with its column (build level) before any geometry
const collectNodes = (rawSteps, levels, recycleSteps, options) => {
    const nodes = [];

    Object.entries(rawSteps).forEach(([name, material], index) => {
        nodes.push({
            id: `0.${LETTERS[index]}`,
            name,
            col: 0,
            material,
            prod: stepProd(material),
            ...stepDetail(stepProd(material), name, options),
        });
    });

    levels.forEach((level, levelIndex) => {
        visibleLevelMaterials(level).forEach(([name, material], index) => {
            nodes.push({
                id: `${levelIndex + 1}.${LETTERS[index]}`,
                name,
                col: levelIndex + 1,
                material,
                prod: stepProd(material),
                ...stepDetail(stepProd(material), name, options),
            });
        });
    });

    const sinkCol = levels.length + 1;
    recycleSteps.forEach((step, index) => {
        nodes.push({
            id: `R.${LETTERS[index]}`,
            name: step.name,
            col: sinkCol,
            material: null,
            prod: null,
            sinkInputs: step.inputs || [],
            footprint: step.footprint ?? null,
            numBuildings: step.num_buildings ?? null,
            monogram: step.footprint?.monogram ?? '',
        });
    });

    return nodes;
};

// flows out of every node — the product's `outputs` map, each byproduct's
// byproducts_used destinations, and sink inputs the maps didn't already carry —
// resolved to edges (dest is a node), finals, or external boundary markers
const collectFlows = (nodes, byproductsUsed) => {
    const byName = new Map(nodes.map((node) => [node.name, node]));
    const edges = [];
    const finals = [];
    const seen = new Set();

    const route = (from, material, dest, qty) => {
        if (!(qty > 0)) {
            return;
        }

        if (dest === 'final') {
            finals.push({ node: from, material, qty, kind: 'final' });

            return;
        }

        const to = byName.get(dest);

        if (!to) {
            finals.push({ node: from, material, qty, kind: 'external', dest });

            return;
        }

        const key = `${from.id}→${to.id}|${material}`;

        if (!seen.has(key)) {
            seen.add(key);
            edges.push({ from, to, material, qty });
        }
    };

    for (const node of nodes) {
        for (const [dest, qty] of Object.entries(node.material?.outputs || {})) {
            route(node, node.name, dest, qty);
        }

        for (const byproduct of Object.keys(node.prod?.byproducts || {})) {
            for (const [dest, qty] of Object.entries(byproductsUsed?.[byproduct] || {})) {
                route(node, byproduct, dest, qty);
            }
        }
    }

    // sink inputs whose flow no outputs map carried (solid recycled byproduct)
    for (const sink of nodes.filter((node) => node.sinkInputs)) {
        for (const { item, qty } of sink.sinkInputs) {
            const from = byName.get(item);

            if (from) {
                route(from, item, sink.name, qty);
            }
        }
    }

    return { edges, finals };
};

// unproduced inputs → import boundary markers (V139: no dangling edges)
const collectImports = (nodes) => {
    const produced = new Set(nodes.map((node) => node.name));
    const imports = [];

    for (const node of nodes) {
        for (const [material, qty] of Object.entries(node.prod?.ingredients || {})) {
            if (!produced.has(material)) {
                imports.push({ node, material, qty });
            }
        }
    }

    return imports;
};

// spread a node's connections along its left (in) or right (out) edge
const attachY = (node, index, count) => node.y + (NODE_H * (index + 1)) / (count + 1);

const assignGeometry = (nodes, edges, finals, imports) => {
    const rowsPerCol = new Map();

    for (const node of nodes) {
        const row = rowsPerCol.get(node.col) ?? 0;

        rowsPerCol.set(node.col, row + 1);
        node.x = MARGIN + node.col * (NODE_W + COL_GAP);
        node.y = MARGIN + row * (NODE_H + ROW_GAP);
        node.w = NODE_W;
        node.h = NODE_H;
    }

    for (const node of nodes) {
        const out = [...edges.filter((edge) => edge.from === node), ...finals.filter((f) => f.node === node)];
        const into = [...edges.filter((edge) => edge.to === node), ...imports.filter((i) => i.node === node)];

        out.forEach((conn, i) => {
            const y = attachY(node, i, out.length);

            if (conn.from === node) {
                conn.x1 = node.x + NODE_W;
                conn.y1 = y;
            } else {
                conn.x = node.x + NODE_W;
                conn.y = y;
            }
        });
        into.forEach((conn, i) => {
            const y = attachY(node, i, into.length);

            if (conn.to === node) {
                conn.x2 = node.x;
                conn.y2 = y;
            } else {
                conn.x = node.x;
                conn.y = y;
            }
        });
    }
};

// full master layout: nodes with geometry, routed edges, boundary markers,
// the distinct legend materials, and the content extent for fit/zoom
export const masterLayout = ({
    rawSteps = {},
    levels = [],
    recycleSteps = [],
    byproductsUsed = {},
    overviews = {},
    buildingMultiples = {},
    designerMk = 'mk1',
    appliedEven = false,
} = {}) => {
    const options = { overviews, buildingMultiples, designerMk, appliedEven };
    const nodes = collectNodes(rawSteps, levels, recycleSteps, options);
    const { edges, finals } = collectFlows(nodes, byproductsUsed);
    const imports = collectImports(nodes);

    assignGeometry(nodes, edges, finals, imports);

    const materials = [...new Set([...edges, ...finals, ...imports].map((conn) => conn.material))].sort((a, b) =>
        a.localeCompare(b)
    );

    const extent = nodes.length
        ? {
              x: 0,
              y: 0,
              w: Math.max(...nodes.map((node) => node.x + node.w)) + MARKER_M + MARGIN,
              h: Math.max(...nodes.map((node) => node.y + node.h)) + MARGIN,
          }
        : { x: 0, y: 0, w: 0, h: 0 };

    return { nodes, edges, finals, imports, materials, extent };
};

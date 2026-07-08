// Manifold + extent geometry for build diagrams (V116/V118/V124).
// Pure functions shared by DiagramSvg (render) and DiagramModal (fit/zoom
// clamps) so both consume one content extent — labels and stagger bands are
// fully visible at fit view, inline and in the modal.

export const FOUNDATION_M = 8;

// Conveyor Splitter / Merger real footprint (satisfactory.wiki.gg), meters (V116)
export const MANIFOLD_M = 4;
// clearance from buildings and between grouped squares (V116)
export const MANIFOLD_GAP_M = 2;
// vertical center-to-center pitch between same-building slots (V118, 3 m per T167)
export const MANIFOLD_STAGGER_M = 3;

export const LABEL_FONT_M = 2.5;
// end-anchored label text extends leftward ~0.6 em per character (V124 estimate)
export const LABEL_CHAR_M = LABEL_FONT_M * 0.6;

// dasharray patterns indexed like the palette — distinct combos per material (V116)
export const MANIFOLD_DASHES = ['2 1', '1 1', '3 1', '0.5 1', '3 1 1 1', '4 2'];

// full slab extent in meters — the pre-V124 view (kept for grouped/no-manifold diagrams)
export const slabExtent = (footprint) => ({
    x: 0,
    y: 0,
    w: footprint.width_foundations * FOUNDATION_M,
    h: footprint.length_foundations * FOUNDATION_M,
});

// V124b: uniform per-diagram stagger pitch. Multi-row diagrams compress so row
// r's output band and row r+1's input band coexist within row_spacing (both
// bands' base depth = 2·(gap + square) = 12 m); p=0 degrades to the pre-V118
// side-by-side layout. Single-row diagrams keep the full 3 m — the content
// extent absorbs any overflow past the slab edge.
export const staggerPitch = (footprint, inSlots, outSlots) => {
    const steps = Math.max(0, inSlots - 1) + Math.max(0, outSlots - 1);

    if (footprint.rows <= 1 || steps === 0) {
        return MANIFOLD_STAGGER_M;
    }

    const available = footprint.row_spacing - 2 * (MANIFOLD_GAP_M + MANIFOLD_M);

    return Math.min(MANIFOLD_STAGGER_M, Math.max(0, available / steps));
};

// one manifold line = per-row band of squares (one per building at this line's
// slot), edge-to-edge trunk segments between consecutive squares, and a stub
// to each building. Geometry per V116: group of N squares centered on the
// building's vertical axis, 2 m in-group gap (compressed when the group would
// outgrow the building width), bands 2 m off the row edge. Band y is never
// clamped to the canvas — overflow expands the content extent instead (V124a)
const manifoldLine = (footprint, line, idx, pitch) => {
    const fp = footprint;
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
        // same-building squares stagger vertically: inputs stack upward away
        // from the row, outputs downward (V118, pitch per V124b)
        const y =
            line.kind === 'input'
                ? rowTop - MANIFOLD_GAP_M - size - line.slot * pitch
                : rowBottom + MANIFOLD_GAP_M + line.slot * pitch;
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
                    : { x1: cx, y1: rowBottom, x2: cx, y2: y }
            );
        }

        for (let b = 0; b < centers.length - 1; b++) {
            trunkSegments.push(
                line.kind === 'input'
                    ? { x1: centers[b] + size / 2, y1: midY, x2: centers[b + 1] - size / 2, y2: midY }
                    : { x1: centers[b + 1] - size / 2, y1: midY, x2: centers[b] + size / 2, y2: midY }
            );
        }

        if (row === 0) {
            labels.push({ x: centers[0] - size / 2 - 1, y: midY });
        }
    }

    return {
        ...line,
        idx,
        dash: MANIFOLD_DASHES[idx % MANIFOLD_DASHES.length],
        monogram: line.pipe ? null : line.kind === 'input' ? 'S' : 'M',
        squares,
        trunkSegments,
        stubs,
        labels,
    };
};

// blueprint I/O port square size + inset from the tile's top/bottom edge (V133)
export const PORT_M = 2;
const PORT_INSET_M = 2;

// blueprint-mode I/O line (V133): splitters/mergers/junctions live inside the
// blueprint, so each material renders as a port row instead — inputs enter each
// tile's LEFT side near the top, outputs exit the left side near the bottom.
// One port per tile, a directional trunk from the slab's left edge feeding the
// ports (inputs flow edge → tiles, outputs tiles → edge), label at the edge.
const blueprintPortLine = (footprint, line, idx) => {
    const fp = footprint;
    const trunkSegments = [];
    const ports = [];
    const labels = [];

    for (let row = 0; row < fp.rows; row++) {
        const count = Math.min(fp.buildings_per_row, fp.num_buildings - row * fp.buildings_per_row);

        if (count <= 0) {
            break;
        }

        const tileTop = fp.top_offset + fp.building_top_offset + row * (fp.building_length + fp.row_spacing);
        const y =
            line.kind === 'input'
                ? tileTop + PORT_INSET_M + line.slot * MANIFOLD_STAGGER_M
                : tileTop + fp.building_length - PORT_INSET_M - line.slot * MANIFOLD_STAGGER_M;

        const stops = Array.from({ length: count }, (_, c) => fp.left_offset + c * fp.building_width);
        stops.forEach((x) => ports.push({ x, y }));

        for (let i = 0; i < stops.length; i++) {
            const from = i === 0 ? 0 : stops[i - 1] + PORT_M / 2;
            const to = stops[i] - PORT_M / 2;

            trunkSegments.push(
                line.kind === 'input' ? { x1: from, y1: y, x2: to, y2: y } : { x1: to, y1: y, x2: from, y2: y }
            );
        }

        if (row === 0) {
            labels.push({ x: -1, y });
        }
    }

    return {
        ...line,
        idx,
        dash: MANIFOLD_DASHES[idx % MANIFOLD_DASHES.length],
        monogram: null,
        squares: [],
        trunkSegments,
        stubs: [],
        ports,
        labels,
    };
};

// all manifold lines for a diagram. `liquids` = Set of liquid/gas names: those
// materials render as PIPE lines — T-junctions instead of splitter/merger
// squares, indexed into their own palette (V132, supersedes V117's filtering).
// Belts and pipes share each side's slot sequence, so mixed bands stagger as
// one group. Grouped (blueprint-mode) footprints render I/O port lines instead
// of manifolds (V133, revises V116's render-nothing clause). Color is NOT
// assigned here — the component picks the mode palette (V125).
export const manifoldLines = (footprint, materials, liquids = null) => {
    const classify = (names, kind) =>
        names.map((material, slot) => ({
            material,
            kind,
            slot,
            slots: names.length,
            pipe: liquids ? liquids.has(material) : false,
        }));

    const inputs = materials ? (materials.inputs ?? []) : ['Input'];
    const outputs = materials ? (materials.outputs ?? []) : ['Output'];
    const lines = [...classify(inputs, 'input'), ...classify(outputs, 'output')];
    const pitch = staggerPitch(footprint, inputs.length, outputs.length);

    // per-class palette indices: belts stay contiguous in the belt palette,
    // pipes in theirs (V132)
    let beltIdx = 0;
    let pipeIdx = 0;

    return lines.map((line) => {
        const idx = line.pipe ? pipeIdx++ : beltIdx++;

        return footprint.grouped
            ? blueprintPortLine(footprint, line, idx)
            : manifoldLine(footprint, line, idx, pitch);
    });
};

// V124a: rendered extent = slab ∪ every manifold square ∪ every label text box.
// Diagrams whose bands and labels already fit return the slab unchanged.
export const contentExtent = (footprint, lines) => {
    const slab = slabExtent(footprint);
    let top = slab.y;
    let bottom = slab.y + slab.h;
    let left = slab.x;

    for (const line of lines) {
        for (const square of line.squares) {
            top = Math.min(top, square.y);
            bottom = Math.max(bottom, square.y + MANIFOLD_M);
        }

        for (const label of line.labels) {
            left = Math.min(left, label.x - line.material.length * LABEL_CHAR_M);
            top = Math.min(top, label.y - LABEL_FONT_M / 2);
            bottom = Math.max(bottom, label.y + LABEL_FONT_M / 2);
        }
    }

    return { x: left, y: top, w: slab.w - left, h: bottom - top };
};

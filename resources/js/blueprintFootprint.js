export const DESIGNER_DIMS = { mk1: 32, mk2: 40, mk3: 48 };

const DESIGNER_KEY = 'bp_designer';

export const loadDesignerMk = (store) => {
    const mk = store.getItem(DESIGNER_KEY, 'mk1');

    return mk in DESIGNER_DIMS ? mk : 'mk1';
};

export const saveDesignerMk = (store, mk) => {
    store.setItem(DESIGNER_KEY, mk in DESIGNER_DIMS ? mk : 'mk1');
};

export const groupedFootprint = (footprint, groupSize, designerM) => {
    const size = Math.max(1, Number.parseInt(groupSize, 10) || 1);
    const tiles = Math.ceil(footprint.num_buildings / size);
    // backend footprint.rows encodes belt_speed + speedLimit — never lay out
    // fewer rows than belts require (V45), capped at one tile per row
    const nearSquareRows = Math.ceil(tiles / Math.ceil(Math.sqrt(tiles)));
    const rows = Math.min(tiles, Math.max(nearSquareRows, footprint.rows));
    const tilesPerRow = Math.ceil(tiles / rows);

    const tileFoundations = Math.ceil(designerM / 8);
    const foundationBorderY = rows > 1;
    const lengthFoundations = (foundationBorderY ? 2 : 0) + rows * (tileFoundations + 2);
    const widthM = designerM * tilesPerRow;
    const widthFoundations = Math.ceil(widthM / 8) + 4;
    const heightWalls = Math.ceil(designerM / 4) + 1;

    const beltLoadInTotal = footprint.belt_load;
    const beltLoadOutTotal = footprint.belt_load_out * footprint.rows;

    const round2 = (n) => Math.round(n * 100) / 100;

    return {
        ...footprint,
        grouped: true,
        group_size: size,
        blueprints: tiles,
        monogram: `${footprint.monogram}x${size}`,
        foundation_border_y: foundationBorderY,
        rows,
        buildings_per_row: tilesPerRow,
        num_buildings: tiles,
        building_length: designerM,
        building_length_foundations: tileFoundations,
        building_width: designerM,
        length_m: rows * designerM,
        length_foundations: lengthFoundations,
        width_m: widthM,
        width_foundations: widthFoundations,
        height_m: designerM,
        height_walls: heightWalls,
        foundations: lengthFoundations * widthFoundations,
        walls: heightWalls * 2 * (lengthFoundations + widthFoundations),
        row_spacing: rows === 1 ? 0 : 8 * (tileFoundations + 2) - designerM,
        top_offset: Math.ceil((8 * (tileFoundations + 2) - designerM) / 2 + (foundationBorderY ? 8 : 0)),
        bottom_offset: Math.floor((8 * (tileFoundations + 2) - designerM) / 2 + (foundationBorderY ? 8 : 0)),
        row_spacing_offset:
            Math.ceil((8 * (tileFoundations + 2) - designerM) / 2 + (foundationBorderY ? 8 : 0)) + designerM,
        left_offset: 16 + (8 * (widthFoundations - 4) - designerM * tilesPerRow) / 2,
        building_top_offset: designerM % 2 ? 0.5 : 0,
        belt_load_in: round2(beltLoadInTotal / rows),
        belt_load_out: round2(beltLoadOutTotal / rows),
        belt_utilization_in: Math.round((100 * beltLoadInTotal) / rows / footprint.belt_speed),
        belt_utilization_out: Math.round((100 * beltLoadOutTotal) / rows / footprint.belt_speed),
    };
};

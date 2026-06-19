// V80: blueprint-aware Fill to 100%. Given a step's current yield (qty), its exact
// fractional machine demand at 100% clock (exactBuildings, backend `exact_buildings`),
// and its effective building multiple X (backend `multiple`, the applied/snapshot
// building_multiples the diagram groups on — T88), return the yield that rounds the
// step's own machine count up to a whole STAMP: a multiple of X at 100% clock.
//
//   ratio = ceil(machines / X) · X / machines      (X = 1 ⇒ ceil(machines)/machines, V55)
//
// Other outputs' net yields are untouched by the caller; only the selected output's
// yield changes. machines is linear in the selected output's net yield when others are
// held fixed, so scaling qty by this ratio lands an exact whole-stamp machine count.
export function fillTargetQty({ qty, exactBuildings, multiple = 1 }) {
    const machines = exactBuildings;

    if (!machines || machines <= 0) {
        return qty;
    }

    const X = Math.max(1, multiple || 1);
    const targetBuildings = Math.ceil(machines / X) * X;

    return qty * (targetBuildings / machines);
}

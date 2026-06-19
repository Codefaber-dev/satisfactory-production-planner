# Satisfactory Production Planner
## satisfactoryproductionplanner.com

### ⛏️ Extraction & Recycling

- Raw resources now build out as real extractors — miners, pumps, and wells with full power and build cost — and an AWESOME Sink recycles unused byproducts into points/min. Source any raw by extract, convert, or unpackage, package-and-sink leftover fluids, and annotate imports with notes.

## v1.2.3 - Extraction & Recycling Update
[Release Notes](#release-notes)

#### By CodeFaber aka [/u/gimcrak](https://www.reddit.com/user/gimcrak)

### 💲 Support
Servers don't grow on trees. If you feel like chipping in that would be toit, but no pressure.

[Donate on Paypal](https://www.paypal.com/donate/?hosted_button_id=LZHQ2LHJQA78Y) or [Become a Patron](https://www.patreon.com/SatisfactoryProductionPlanner) today and help support ongoing development.

#### Donations to date: $700

### 🗣️ Discord
Eager to share feedback or have suggestions for improvements?

[Join The Discussion On Discord](https://discord.gg/dqGQECppCy)

### 👤 User Registration
Your factories and favorites will persist across sessions if you [register](https://satisfactoryproductionplanner.com), but it's entirely optional. 

### 🔏️ Privacy
I hate spam as much as the next guy. I will never send you spam or give your information out to anyone.

## <a name="release-notes"></a>
##  📔 Release Notes
Greetings Pioneers! Here are the latest changes to the Satisfactory Production Planner.

### v1.2.3

- ⛏️ **Resource Extraction** — Raw resources now appear as their own production steps at the top of the plan, each with a source picker. Choose **Extract** to add real extractors — Miner Mk.1/2/3, Water Extractor, Oil Extractor, or Resource Well Pressurizer — with node purity, miner tier, and power-shard settings. Extractors are counted in the building summary and parts/build-cost list, and their power is rolled into the plan total instead of a rough proxy estimate.
- 🔀 **Flexible Raw Sourcing** — Beyond extraction, source any raw by **Import** (default), **Convert** (produce one ore from another via a Converter recipe), or **Unpackage** (e.g. get Water by unpackaging Packaged Water). Convert/unpackage chains that loop back on themselves are solved by the equation solver, not dodged.
- ♻️ **AWESOME Sink Recycling** — The plan now reports total **points/min** from sinking unused byproducts, surfaced as a terminal **Recycling** step. A new **Package & Sink Fluids** toggle in Building Settings packages leftover fluids and gases (which can't be sunk directly) and sinks the packaged form — the added Packager and AWESOME Sink render as full build steps with their own diagrams, building counts, and power.
- 🛈 **Import Notes** — Add a free-text note to any imported ingredient (e.g. "From NW Steel Factory"). Notes show on the ingredient's production step and persist with saved factories.
- 🎯 **Perfect Ratio Factory** — A new button in Plan Settings scales the whole plan to the smallest size where every building runs whole at 100% clock — no fractional or underclocked machines anywhere. Works for mutual-recipe loops too, and is now **blueprint-aware**: when a building type is grouped into blueprint stamps, the ratio fills whole stamps. If the perfect ratio would make the factory impractically large, it warns instead of applying.
- 📐 **Blueprint-aware Fill to 100%** — The per-output "Fill to 100%" button now rounds up to a whole blueprint stamp for grouped building types, so no partial stamps or underclocked machines.
- 🪲 Bug fix: Loop steps no longer show **"Infinity%"** for an input — the usage percentage now divides by the correct plan total when the output product feeds back into a loop.
- 🪲 Bug fix: The **Package & Sink Fluids** toggle now correctly sizes leftover fluids — recycling read fully-consumed byproducts as leftover, making the toggle appear to do nothing.

### v1.2.2

- 🔄 **Circular Recipes Solved** — Recipes that feed each other — like **Recycled Plastic ⇄ Recycled Rubber** — are now solved as a proper system of equations, keeping the recipes you chose. The planner works out exactly how much of each to make instead of silently swapping a recipe to dodge the loop.
- 🎯 **Fill to 100%** — A new per-output button bumps an output up to the amount that runs its buildings at a clean 100% clock, without changing your other outputs.
- 🧩 **Smarter Loop Handling** — When a loop genuinely can't be solved (e.g. packaging and immediately unpackaging the same fluid), the planner auto-sources it from a non-looping recipe and respects your intent: choosing **Unpackage Fuel** keeps that choice and sources Packaged Fuel elsewhere. You can also just **import** the looped item to break it yourself.
- 🪲 Bug fix: Saved factories no longer revert their recipe choices on reload — sub-recipe selections now persist correctly.
- 🛈 The alarming "Circular Dependencies Found" warning is now an informational **"Recipes auto-adjusted to resolve a loop"** note, with a hint to change the recipe or import the item.
- 🪲 Bug fix: Quick Nav no longer lists imported products — only the steps actually shown in the plan appear in the navigator.

---

### v1.2.1

- 📐 **Blueprint Grouping** — Build diagrams can now group buildings into blueprint-sized tiles. Set a blueprint size for each building type and the diagram will show how many blueprint stamps you need instead of individual buildings. Footprint stats (foundations, walls, offsets) update to match.
- 📏 **Blueprint Designer Tiers** — Choose your Blueprint Designer mark (Mk.1, Mk.2, or Mk.3) and tiles will reflect the correct dimensions. Your choice is remembered across visits.
- 🏗️ **Building Settings Panel** — New panel for configuring blueprint sizes and toggles. Sizes are saved globally so they carry over between factories, while on/off toggles are remembered per factory. Make all your changes, then hit **Update** to apply them at once — no more waiting for the page to refresh after every input.
- 🔢 **Building Count Multiples** — Round building counts up to multiples of your choosing (e.g. always plan in groups of 4 constructors) so your plans match how you actually build.
- 💲 **Recipe Cost Multiplier** — Scale ingredient costs up or down for a whole plan. Useful for modded playthroughs or what-if scenarios.
- 🏗️ **Building Cost Multiplier** — Scale building construction costs independently of recipe costs.
- ⚡ **Power Cost Multiplier** — Adjust power consumption across your plan the same way.
- ⚙️ **Plan Settings Panel** — Belt speed and the cost/building/power multipliers now live in the Settings panel as easy-to-scan cards with icons, instead of crowding the header. The panel is available even before you've added any buildings.
- ☑️ **Checklist Updated** — The Production Checklist now includes all the new v1.2 buildings and recipes, with deprecated items removed.
- 📱 **Mobile Improvements** — A responsive design pass across the planner: no more horizontal scrolling on phones, touch-friendlier recipe selectors, and layouts that stack properly on small screens.
- 🪲 Bug fix: Somersloop settings now apply correctly to production steps.
- 🪲 Bug fix: Cost multipliers now carry through the entire production chain correctly.
- 🪲 Bug fix: Grouped diagrams now account for rows added due to belt speed limits.
- 🪲 Bug fix: Force-even rows now works with blueprint grouping — the stamp grid fills out evenly instead of leaving a ragged last row.
- 🪲 Bug fix: Foundation/footprint stats no longer inflate when force-even rows is combined with blueprint grouping.

---

### v1.2.0

- 🗄️ All Satisfactory v1.0 endgame and Update 6 content was already in the game but missing from the database seeder. This update backports it all.
- 🏭 New buildings: **Converter** and **Quantum Encoder** now available in the Production Planner.
- ⚗️ New recipe chains: SAM processing, Dark Matter, Diamonds, Ficsonium, Time Crystal, Ficsite Ingots, Quantum Encoder recipes, ore-conversion alts, Ionized Fuel, Rocket Fuel, and all Update 6 combat recipes.
- 🔋 Power Planner: **Ficsonium Fuel Rod** added to Nuclear Power Plant. **Rocket Fuel** and **Ionized Fuel** now available as Fuel Generator options.
- 🪲 Recipe corrections: Iron Alloy Ingot and Copper Alloy Ingot rates updated to v1.1 values.
- 🪲 Bug fix: Nuclear Power Plant now correctly generates power instead of consuming it. Net power output is now correct.
- 🏷️ Renamed ingredients to canonical names: SAM Ore → SAM, Green Power Slug → Blue Power Slug.
- 🗑️ Removed deprecated items and recipes: Alien Carapace, Beacon, Color Cartridge, Rifle Cartridge, Spiked Rebar, Steel Coated Plate.

---

### v1.0.1

- 🪲 This update brings several recipe fixes. Thank you to everyone who pointed those out.
- Recipes fixed: Cooling Device, Iron Alloy Ingot, Automated Speed Wiring, Leached Iron Ingot, Leached Copper Ingot, Leached Caterium Ingot, Copper Alloy Ingot, Dark Matter Residue, Remove Beacon, Remove Color Cartridge, Remove Steel Coated Plate, Adaptive Control Unit, Fine Concrete, Rubber Concrete, Steel Canister, Explosive Rebar, Gas Filter, Fine Black Powder, Coated Iron Plate, Automated Miner, Radio Control Unit, Radio Connection Unit, Adhered Iron Plate, Copper Rotor, Shatter Rebar, Cheap Silica, Compacted Steel Ingot

--- 

### Previous Releases

### v1.0.0

- 🎉 1.0 Is finally here. This update brings new recipes, new buildings, new products, and tweaks to several existing recipes.
- 🪲 Please let me know if you spot any mistakes in the recipes, calculations, etc. You can find me in the [discord](https://discord.gg/dqGQECppCy).
- 🙏 A huge thank you to all those who have donated, it really helps with costs.

### v0.4.0
Quality Of Life Improvements 
- You can now select how building rows are broken out to avoid over-saturating belts.
  - Default behavior is to rate limit input and output belt speeds
  - Clicking the Limit Belt Rates button will toggle between the options: Inputs & Outputs, just Inputs, or just Outputs
  - E.g. if you limit outputs, then inputs will be allowed to exceed your set max belt speed and vice versa
![Speed Limits Button](https://res.cloudinary.com/codefaber/image/upload/v1700288200/satisfactory/speed-limit-button.png)
  - Added max input and output rate stats to diagram legend
![Speed Limits](https://res.cloudinary.com/codefaber/image/upload/v1700288200/satisfactory/speed-limit-toggle.png)
- Removed auto-update behavior from many form elements in favor of Update button
- Fixed power calculation method (reduced exponent from 1.6 to 1.321928 as of U7)
- Fixed parts list and building summary incorrectly showing buildings for imported products


### v0.3.9
New Feature: Production Checklist ☑️
- Track your progression with an interactive checklist
- Try it now [here](/checklist)

![Production Checklist](https://res.cloudinary.com/codefaber/image/upload/v1696719094/satisfactory/production-checklist.png)


### v0.3.8
Minor Fixes
- Fixed issue with sending password reset emails
- Fixed issue with saving changes to factories with multiple outputs


### v0.3.7
Minor Fixes
- Fixed yield per minute of High-Speed Connector Recipe
- Removed Seismic Nobelisk Alt
- Fixed an issue with recycling byproducts with multiple outputs producing/consuming the same byproduct. It mistakenly count the same byproduct multiple times.


### v0.3.6
Added FICSMAS Event 🎄🌟❄️
- Added FICSMAS Products to Production Planner
- Added missing default recipe for Cooling System
- Various bug fixes


### v0.3.5
- Fixed a bug where the production calculator would fail to take belt speed into account when calculating number of buildings required.

### v0.3.4
One-click Clock Speed Maximizing ⏱️
- You can now choose to maximize the clock speed of an individual production step (credit to @CharlesMB on discord)
- Output will be adjusted to run clock at max speed (e.g. 100%, 150%, etc.)
- You can choose to add the extra items as an additional output or scale up the entire production line

![Clock Speed Maximizer](https://res.cloudinary.com/codefaber/image/upload/v1668382230/satisfactory/maximize-clock-speed.png)

### v0.3.3
Better Step Navigation ➡️
- Production steps are now labeled by level (e.g. 1.A, 3.B)
- Destination details now have links that take you to the relevant production step

![Better Step Navigation](https://res.cloudinary.com/codefaber/image/upload/v1668285571/satisfactory/better-step-navigation.png)

### v0.3.2
Improved Byproduct Recycling ♻️
- Raw ingredient byproducts (e.g. water) will now be recycled if possible.
- Added additional value in parentheses where byproducts are used as inputs.
- Bug fix. Fixed an issue that prevented favorite recipes from being used correctly.

![Better Recycling](https://res.cloudinary.com/codefaber/image/upload/v1667716128/satisfactory/better-recycling.png)

### v0.3.1
Byproduct Recycling ♻️
- Production steps will now utilize byproducts produced in other steps
- This also works for [multi-product lines](<https://satisfactoryproductionplanner.com/dashboard/multi?imports=&belt_speed=780&variant=mk1&choices[Fuel]=Residual Fuel&even=0&product[]=Packaged Fuel&product[]=Plastic&yield[]=100&yield[]=250&recipe[]=Packaged Fuel&recipe[]=Plastic&multiFactory=>)


### v0.3.0
Update 6 & New Domain
- Satisfactory Production Planner has been updated with the changes introduced in Update 6 including new items and tweaks to existing recipes. Please let me know if you spot any errors and I will work to get those corrected. Enjoy.
- This project has a new home now at satisfactoryproductionplanner.com. If you have existing shortcuts to the old domain, they will continue to work and redirect to the new one.


___
### v0.2.0
- 📊 Diagram Improvements - diagrams now display offset lengths to aid in positioning. When more than 1 row is required, the Row Spacing length is also displayed. Users of the Smart! mod can set the Y spacing value to this length to achieve the displayed spacing. To set the Y spacing (default keymappings), hold P, press the up arrow, then scroll the mouse wheel until the spacing is correct.


![Overclocking](https://res.cloudinary.com/codefaber/image/upload/v1645676655/satisfactory/offsets.png)

### v0.1.0
- UI Improvements and bug fixes


### v0.0.9
New Feature
- Overclocking ⏱️ Production Planner now supports overclocking for individual products. Selecting a different "Max Clock" will automatically update diagrams and power calculations and show the number of power shards required.

![Overclocking](https://res.cloudinary.com/codefaber/image/upload/c_scale,w_316/v1645676655/satisfactory/overclocking.png)

![Power Summary](https://res.cloudinary.com/codefaber/image/upload/v1645676990/satisfactory/PowerSummary.png)


### v0.0.8
New Features
- Multiple Outputs 🎉 Production Planner now supports up to 6 products from the same production line. [Check It Out](https://satisfactoryproductionplanner.com/dashboard/multi?imports=Iron+Ingot,Caterium+Ingot,Screw,Copper+Ingot&variant=mk1&product[0]=Motor&product[1]=Rotor&product[2]=Stator&yield[0]=10&yield[1]=10&yield[2]=10&recipe[0]=Motor&recipe[1]=Rotor&recipe[2]=Quickwire+Stator&choices[Steel+Ingot]=Solid+Steel+Ingot&choices[Iron+Rod]=Steel+Rod)
- Force even rows in build diagrams. You can optionally force symmetrical rows of buildings in the production planner. Build costs and power calculations will update to reflect the selected configuration.

Fixes
- Recipe Picker improvements. Icons now indicate information about the selected recipe
  - ⬜ Default Recipe
  - ⏺️ Chosen Recipe (for the production line)
  - ⭐ Favorite Recipe


### v0.0.7

Fixed Bugs
- Fixed issues with saving factories and viewing saved factories. Error happened with guests and logged-in users when saving factories using default recipe.
- Fixed error when importing all intermediate products.


### v0.0.6

Fixed bugs
- Fixed an issue with guest sessions not being uniquely identified - should resolve a lot of issues around incorrect default recipes. 
- Fixed missing Uranium Icon

Planned features
- Power Planner - Display required alt recipes and buildings for fuel types
- Power Planner - Specify input and quantity and build plan automatically
- Power Planner - Calculate net power including cost of producing fuel
- Production Planner - Calculate multiple outputs


### v0.0.5

- New Feature - [Power Planner](https://satisfactory.codefaber.dev/power) Enter your desired power output and get a detailed breakdown by generator type


### v0.0.4

- Huge improvements to calculations. Rewrote calculation engine... twice.
- Reworked circular dependency handling. Incompatible recipes are automatically overridden when necessary and a warning is displayed. Previously it would just elect to import offending products to avoid circular dependencies.
- Added recipe favorite "presets" and option to reset all recipes to default.
- Fixed some recipes with incorrect values. Thank you to /u/oursondechine for help with the QC work.
- Improved energy/power calculations. Note: I'm using a different method for calculating extraction energy than the wiki does. 
- General performance improvements.

### v0.0.3

 - Can now specify whether to produce (default) or import intermediate products.
 - Destination product(s) and percentage distribution(s) are now displayed for intermediate products.
 - Can now add notes to saved factories in "My Factories". Formatting notes with markdown is supported.
 - Saved factories will now remember and display products that are designated as "imports".
 -  

### v0.0.2
New Features
- Dark Mode
- Save production lines to "My Factories". Will eventually expand on this functionality later.
- Selectively ignore certain raw ingredients or specify limiting ingredients when updating yield. Each raw ingredient has a toggle button now. Click to disable that ingredient in the recalculation. Thanks to /u/Red49er for the suggestion.
- Added a page to manage My Favorite Recipes

Planned
- Use products from existing production lines as inputs

Fixed Issues
- Fixed an issue where diagrams toggle would reset with each page refresh
- Fixed an issue where logged-in users were still seeing Guest options

Known Issues
- Unpackage fuel recipe gives error 500 message. edit: seems to be an infinite recursion issue, workaround is to change the packaged fuel recipe to "Diluted Packaged Fuel".

### v0.0.1
Features
 - "Favorite" recipes. Choose a favorite recipe, and it will automatically be used whenever that part is needed.
 - Selectable belt speed. Diagrams will automatically adjust to accommodate chosen belt speed.
 - MK++ compatible. Select mk2, mk3, or mk4 for some or all production lines and diagrams and parts lists will be updated automatically.
 - Adjust yield by available raw materials.
 - Optional user registration. Your favorites will be saved to your user profile.



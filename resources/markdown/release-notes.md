# Satisfactory Production Planner

## v0.2.0
[Release Notes](#release-notes)

#### By CodeFaber aka [/u/gimcrak](https://www.reddit.com/user/gimcrak)

### 🗣️ Discord
Eager to share feedback or have suggestions for improvements?

[Join The Discussion On Discord](https://discord.gg/dqGQECppCy)

### 👤 User Registration
Your factories and favorites will persist across sessions if you [register](https://satisfactory.codefaber.dev), but it's entirely optional. 

### 🔏️ Privacy
I hate spam as much as the next guy. I will never send you spam or give your information out to anyone.

### 💲 Support
Servers don't grow on trees. If you feel like chipping in that would be toit, but no pressure.

🙏 I want to say a huge thank you to those who have donated, it is greatly appreciated.

[Donate on Paypal](https://www.paypal.com/donate/?hosted_button_id=LZHQ2LHJQA78Y)

## <a name="release-notes"></a>


##  📔 Release Notes
Greetings Pioneers! Here are the latest changes to the Satisfactory Production Planner.

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
- Multiple Outputs 🎉 Production Planner now supports up to 6 products from the same production line. [Check It Out](/dashboard/multi?imports=Iron+Ingot,Caterium+Ingot,Screw,Copper+Ingot&variant=mk1&product[0]=Motor&product[1]=Rotor&product[2]=Stator&yield[0]=10&yield[1]=10&yield[2]=10&recipe[0]=Motor&recipe[1]=Rotor&recipe[2]=Quickwire+Stator&choices[Steel+Ingot]=Solid+Steel+Ingot&choices[Iron+Rod]=Steel+Rod)
- Force even rows in build diagrams. You can optionally force symmetrical rows of buildings in the production planner. Build costs and power calculations will update to reflect the selected configuration.

Fixes
- Recipe Picker improvements. Icons now indicate information about the selected recipe
  - ⬜ Default Recipe
  - ⏺️ Chosen Recipe (for the production line)
  - ⭐ Favorite Recipe


### v0.0.7

Fixed Bugs
- Fixed issues with saving factories and viewing saved factories. Error happened with guests and logged in users when saving factories using default recipe.
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
- Fixed an issue where logged in users were still seeing Guest options

Known Issues
- Unpackage fuel recipe gives error 500 message. edit: seems to be an infinite recursion issue, workaround is to change the packaged fuel recipe to "Diluted Packaged Fuel".

### v0.0.1
Features
 - "Favorite" recipes. Choose a favorite recipe and it will automatically be used whenever that part is needed.
 - Selectable belt speed. Diagrams will automatically adjust to accommodate chosen belt speed.
 - MK++ compatible. Select mk2, mk3, or mk4 for some or all production lines and diagrams and parts lists will be updated automatically.
 - Adjust yield by available raw materials.
 - Optional user registration. Your favorites will be saved to your user profile.



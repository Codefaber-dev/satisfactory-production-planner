# Satisfactory Production Planner
By CodeFaber

[Join The Discussion On Discord](https://discord.gg/dqGQECppCy)

## Release Notes
Greetings pioneers! Here are the latest changes to the Satisfactory Production Planner.

___
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


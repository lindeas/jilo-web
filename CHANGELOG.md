# Changelog

All notable changes to this project will be documented in this file.

---

## Unreleased

#### Links
- upstream: https://code.lindeas.com/lindeas/jilo-web/compare/v0.2.1...HEAD
- codeberg: https://codeberg.org/lindeas/jilo-web/compare/v0.2.1...HEAD
- github: https://github.com/lindeas/jilo-web/compare/v0.2.1...HEAD
- gitlab: https://gitlab.com/lindeas/jilo-web/-/compare/v0.2.1...HEAD

### Added
- Added Jilo Server check and notice on error
- Added status page
- Added Jilo agents status checks


---

## 0.2.1 - 2024-10-17

#### Links
- upstream: https://code.lindeas.com/lindeas/jilo-web/compare/v0.2...v0.2.1
- codeberg: https://codeberg.org/lindeas/jilo-web/compare/v0.2...v0.2.1
- github: https://github.com/lindeas/jilo-web/compare/v0.2...v0.2.1
- gitlab: https://gitlab.com/lindeas/jilo-web/-/compare/v0.2...v0.2.1

### Added
- Added support for managing Jilo Agents
- Authenticating to Jilo Agents with JWT tokens with a shared secret key
- Added Jilo Agent functionality to fetch data, cache it and manage the cache
- Added more fields and avatar image to user profile
- Added pagination (with ellipses) for the longer listings
- Added initial support for application logs
- Added help page
- Added support for graphs by Chart.js
- Added "graphs" section in sidebar with graphs and latest data pages

### Changed
- Jitsi platforms config moved from file to SQLite database
- Left sidebar menu items reordered

### Fixed
- All output HTML sanitized
- Sanitized input forms data
- Fixed error in calculation of monthly total conferences on front page

---

## 0.2 - 2024-08-31

#### Links
- upstream: https://code.lindeas.com/lindeas/jilo-web/compare/v0.1.1...v0.2
- codeberg: https://codeberg.org/lindeas/jilo-web/compare/v0.1.1...v0.2
- github: https://github.com/lindeas/jilo-web/compare/v0.1.1...v0.2
- gitlab: https://gitlab.com/lindeas/jilo-web/-/compare/v0.1.1...v0.2

### Added
- Added collapsible front page widgets
- Added widgets to conferences, participants and components pages
- Added front page widget for monthly conferences and participants number
- Added login/registration control and messages
- Added default config file locations
- Added left collapsible sidebar
- Added logo
- Added database helper functions
- Added support for multiple Jitsi platforms
- Added app environments "production" and "development"
- Added visualisation of config.js and interface_config.js per Jitsi platform

### Changed
- MVC design - models(classes folder), views(templates folder) and controllers(pages folder)
- Changed menus - categories on sidebar menu, jitsi platforms on top menu
- Moved all the app code outside of the public web folder
- Config file now can have nested arrays

### Fixed
- Fixed SQL when conferences start and end time are not explicitly clear
- Web design fixes
- Fixed install script

---

## 0.1.1 - 2024-07-25

#### Links
- upstream: https://code.lindeas.com/lindeas/jilo-web/compare/v0.1...v0.1.1
- codeberg: https://codeberg.org/lindeas/jilo-web/compare/v0.1...v0.1.1
- github: https://github.com/lindeas/jilo-web/compare/v0.1...v0.1.1
- gitlab: https://gitlab.com/lindeas/jilo-web/-/compare/v0.1...v0.1.1

### Added
- Added duration calculation in conferences listing
- Added manual install script
- Added DEB and RPM build files
- Added Bootstrap (licensed under MIT)

### Changed
- Changed the layout with bootstrap CSS classes

### Fixed

---

## 0.1 - 2024-07-08

#### Links
- upstream: https://code.lindeas.com/lindeas/jilo-web/releases/tag/v0.1
- codeberg: https://codeberg.org/lindeas/jilo-web/releases/tag/v0.1
- github: https://github.com/lindeas/jilo-web/releases/tag/v0.1
- gitlab: https://gitlab.com/lindeas/jilo-web/-/releases/v0.1

### Added
- Initial version
- Added login and registration
- Added session persistence and cookies
- Added config page with configuration details
- Added conferences page with listing of conferences from the jilo database
- Added search filter for conferences with time, ID and name
- Added front page widgets
- Added demo installation on https://work.lindeas.com/jilo-web-demo/
- Added participant search page
- Added component events search page

# Changelog

All notable changes to this project will be documented in this file.

---

## Unreleased

#### Links
- upstream: https://code.lindeas.com/lindeas/jilo-web/compare/v0.4.1...HEAD
- codeberg: https://codeberg.org/lindeas/jilo-web/compare/v0.4.1...HEAD
- github: https://github.com/lindeas/jilo-web/compare/v0.4.1...HEAD
- gitlab: https://gitlab.com/lindeas/jilo-web/-/compare/v0.4.1...HEAD

### Added
- CSS for dashboard widgets
- Monthly dashboard statistics redesign
- Tracking of applied database migrations in the database
- Option to run database migrations one by one
- Log Throttler to prevent log flooding
- Logger helper with fallback when no log plugin is available
- Plugin asset page
- Plugin hooks for profile page, account menu, and asset loading
- Email helper and email templates, including password reset email
- Admin page and admin dashboard for all administrative tasks
- Plugin namagement section for the admin dashboard

### Changed
- Updated credentials pages and removed unused "credentials.php"
- Updated credentials pages and removed unused "credentials.php"
- Redesigned admin tools, themes, profile, credentials/2FA, and authentication pages
- Redesigned sidebar, main elements, menus, and overall CSS
- Updated pagination styling
- Reorganized dashboard layout
- Switched profile edit and action pages to uniform action-card design
- Replaced "error_log" with "app_log" in 2FA
- Updated index bootstrap to use global "APP_PATH"
- Refactored database migration system and Admin Tools functionality

### Fixed
- Database migration reliability issues
- Validator rejecting "0" as a valid value
- Collapsing sidebar layout issues
- Profile avatar upload issues
- Public pages incorrectly requiring authentication
- Correct encoding of login redirect URL parameters

---

## 0.4.1 - 2025-11-13

#### Links
- upstream: https://code.lindeas.com/lindeas/jilo-web/compare/v0.4...v0.4.1
- codeberg: https://codeberg.org/lindeas/jilo-web/compare/v0.4...v0.4.1
- github: https://github.com/lindeas/jilo-web/compare/v0.4...v0.4.1
- gitlab: https://gitlab.com/lindeas/jilo-web/-/compare/v0.4...v0.4.1

### Added
- Added the ability to have non-sanitized feedback messages
- Added a notice for maintenance mode for superusers
- Added initial support for maintenance mode
- Added initial support for database upgrades and migrations
- Added CSRF protection to the theme switcher functionality
- Added a helper to manage all static assets of a theme
- Added theme data to be passed correctly to the views
- Added "modern" and "retro" theme screenshots
- Added "alternative retro" theme
- Added CSS and JS to the default theme
- Added change theme menu entry

### Changed
- Moved away from SQLite to MariaDB/MySQL for the main Jilo website
- Integrated Highlight.js library into the SQL view modal for better code highlighting
- Moved the migration flag to the database with a fallback to a file
- Made the theme setting configuration per-user instead of global
- Refactored the session class and added a random session name generator if not configured
- Moved session variables to the configuration file

### Fixed
- Fixed flash messages to show up only once per page
- Fixed database migration functionality and associated feedback notices
- Fixed theme folder structure, helpers, and display logic to work correctly with new asset management
- Fixed index routing to work with the latest session and config changes
- Fixed the router class and several bugs within the session class and theme switcher functionality

### Removed
- Removed getScreenshotUrl function, using the generic getAssetUrl for all assets instead

---

## 0.4 - 2025-04-12

#### Links
- upstream: https://code.lindeas.com/lindeas/jilo-web/compare/v0.3...v0.4
- codeberg: https://codeberg.org/lindeas/jilo-web/compare/v0.3...v0.4
- github: https://github.com/lindeas/jilo-web/compare/v0.3...v0.4
- gitlab: https://gitlab.com/lindeas/jilo-web/-/compare/v0.3...v0.4

### Added
- Added top-right menu with profile, admin, and docs sections
- Added two-factor authentication
- Added resetting of forgotten password
- Added login credentials management
- Added proper pagination
- Added agents managemet pages
- Added javascript-based feedback messages
- Added description to each page
- Added CSRF checks
- Added validator class for all forms
- Added rate limiting to all pages
- Added authentication rate limiting to login and registration
- Added unit tests
- Added integration/feature tests
- Added testing workflow for github

### Changed
- Increased session to 2 hours w/out "remember me", 30 days with
- Made the config editing in-place with AJAX
- Redesigned the help page
- Moved graphs and latest data to their own pages
- Moved live config.js to its own page
- Redesigned the messages system and renamed them to feedback messages

### Fixed
- Bugfixes
- Fixed config editing
- Fixed logs search
- Removed hardcoded messages, changed to feedback messages

---

## 0.3 - 2025-01-15

#### Links
- upstream: https://code.lindeas.com/lindeas/jilo-web/compare/v0.2.1...v0.3
- codeberg: https://codeberg.org/lindeas/jilo-web/compare/v0.2.1...v0.3
- github: https://github.com/lindeas/jilo-web/compare/v0.2.1...v0.3
- gitlab: https://gitlab.com/lindeas/jilo-web/-/compare/v0.2.1...v0.3

### Added
- Added status page
- Added latest data page
- Added graphs page
- Added Jilo agents status checks
- Added periodic Jilo agents checks
- Added Jilo Server check and notice on error
- Added "jitsi platforms config" section in the sidebar
- Added editing for platforms
- Added editing for hosts
- Added editing for the Jilo configuration file
- Added phpdoc comments
- Added rate limiting for login with blacklist and whitelist
- Added a page for configuring the rate limiting

### Changed
- Implemented a new messaging and notifications system
- Moved all live checks pages to the "live data" sidebar section
- Separated the config page to multiple pages
- Moved the config pages to "jitsi platforms config" section

### Fixed
- Fixed bugs in config editing pages and cleaned up the HTML

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

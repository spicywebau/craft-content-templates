# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

### Fixed
- Fixed a bug where the 'Choose a template' modal would reappear when reloading a new draft with changes made to any of its field values, as long as the slug had not been set

## 1.0.3 - 2023-08-25

### Fixed
- Fixed a bug where the 'Choose a template' modal would not appear when creating a new entry with fields with non-empty default values
- Fixed an "Element query executed before Craft is fully initialized" warning in the Craft logs that was caused by Content Templates

## 1.0.2 - 2023-08-23

### Fixed
- Fixed an error that occurred when saving a content template if the Preparse Field plugin is also installed

## 1.0.1 - 2023-07-14

### Fixed
- Fixed a bug where content templates could not be created for entry types that didn't yet have a content template structure created
- Fixed an error that occurred when deleting a content template draft, if the associated entry type had no other content templates

## 1.0.0 - 2023-06-13

### Added
- Initial release

# Changelog
All notable changes to the AI Check plugin will be documented in this file.

## [1.0.0] - 2026-01-18
### Added
- Initial release
- Student-facing AI feedback system for assignment submissions
- Automatic grade level detection from username (graduation year parsing)
- Age-appropriate feedback for grades 3-12
- Support for multiple submission types:
  - Online text submissions
  - File uploads (text files, with extensibility for other formats)
- External Services API implementation for AJAX calls
- Privacy API implementation (GDPR compliant)
- System-wide configuration (no per-assignment setup needed)
- Automatic rubric detection from assignment files
- Modal feedback display
- Localized language strings

### Features
- Button injection on assignment submission pages
- Real-time AI feedback without page reload
- Uses Moodle AI subsystem for AI provider integration
- Feedback only - no grade storage or gradebook impact
- Student-only access (teachers cannot use on student work)
- Responsive modal dialog for feedback display

### Technical
- Moodle 4.5+ compatibility
- PHP 7.4+ compatibility
- Proper frankenstyle naming throughout
- External Services for AJAX (not direct endpoints)
- AMD JavaScript modules (no inline JavaScript)
- Privacy metadata provider implementation
- Coding standards compliance

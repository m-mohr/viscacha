# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.8.2] - 2020-??-??

**This release has breaking changes!** It removes a lot of (mostly) unused features and simplifies features to keep maintainability manageable. 

### Added

- Attachments can be embedded into the post text
- New Password are stored with a more secure hashing algorithm
- Pagination in "my last posts"

### Changed

- Responsive design
- Edit history is shown on separate page instead of in a popup
- Success and error messages are not shown on a separate page anylonger to improve working speed in the forum
- Internal refactoring
- Design management via File Explorer
- Offline message stored in config file
- Simplified "who is online"
- Template `menu` now in `header` (may need template changes)
- PM index is now directly showing the inbox

### Removed

- Newsfeeds (Atom, RSS, ...)
- Post-Ratings, Topic-Ratings and Member-Ratings
- Private notices in user profile
- File type management and file type details for attachments
- Show a single post in a popup window. Link to post anchor instead
- Print view. Improved default stylesheet instead
- Instant Messaging clients from profile. Use custom profile fields instead. Also removes IM Online Status management in Admin CP
- Simple Admin CP mode
- Bot- and crawler management. Bots and crawlers are now listed as guests
- TAR support. Use ZIP instead
- Don't show file type icons in file explorer
- Persistance mode for database connection
- PHP MySQL driver. Use PHP MySQLi instead
- Support for PHP 5 and the Suhosin patch
- Glossary management
- Vocabulary management
- Syntax highlighting
- Database optimization tools in Admin CP
- Database status vire in Admin CP
- VeriWord Captchas
- Limit moderator permissions by time
- Topic status categories (news, article, ...)
- Fine-grained language file management in Admin CP
- Newsletter management in Admin CP and respective user setting
- Disposable e-mail management in Admin CP. Disposable e-mails can't be blocked from registration any longer
- Cache Detail View in Admin CP
- Support for GD version 1.0
- Configurable Session ID length
- Disabling online Status of users
- Limit download speed for attachments
- GZIP compression. Use HTTP server settings instead.
- "Useful" links on Admin CP index page.
- Support for legacy browsers (Internet Explorer < 9)
- Downloading code from BB-Code `code` tags
- Highliting of search keywords in posts

### Fixed
- Fix timeout for overly large folders in file explorer
- PHP 7 compatibility
- Mass management of topics keeps track of pagination
- Prevent users from being logged in twice
- Various other bugs

### Deprecated
- BB-Code list types `I` and `i`
- BB-Code tag `note`
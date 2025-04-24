# Changelog: Cli

> version: $Id$ ($Date$)

## History

### current: 0.16.0-dev (2025 Mar xx)

 - menu  : now takes int and string values for menu index
 - update: `Pencil::pad` now has a padString argument
 - update: `Notify` improved parametar types

### current: 0.15.0 (2025 Mar 14)

 - fix: `Lexer::unshift` return type
 - fix: `Arguments::parseFlag` not incrementing stackable flags
 - other minor updates and fixes (mainly to code comments & variable typing)
 - switch from `posix_isatty` to `stream_isatty` for better compatibility
 - windows compatibility improvements
 - update return & param types
 - update code docs
 - simplify inter-class function calls
 - improved File writing

### 0.14.1 (2023 May 03)

 - new: `Cli::shellEnv` return enum Environment type: none, interactive, non-interactive
 - update: minor tweaks, improvements and updates

### 0.14.0 (2022 Dec 21)

 - new: `Pencil::format` returns colour string without outputting it
 - new: `Pencil::input` reads terminal input
 - new: `Pencil` cache original text for reversing it in certain situations
 - new: `Colour::Default` system colour
 - fix: `Arguments::getOption` return type
 - fix: various little updates and fixes
 - update: `Arguments` implements `\Inane\Stdlib\Converters\JSONable`
 - update: `Arguments::toJSON` added $pretty option to format json
 - update: `HelpScreen` fixes for *php 8.1*
 - update: `\Inane\Stdlib\Json` start to replace `json_*`
 - update: **inanepain/stdlib** requirement bumped to **0.4.0**

### 0.13.0 (2022 Oct 10)

 - fix: STD(OUT/IN/ERR) undefined
 - new: `Cli::isCliServer` Is PHP built-in server
 - new: `Pencil` stationary tools
 - upd: `Cli::input` new optional arg `$default` returned if non-interactive shell
 - many minor tweaks, updates and fixes

### 0.12.1 (2022 Aug 11)

 - fix: `TextTable` `divider` => uses full column width
 - fix: `TextTable` coloured string cell width
 - fix: `Colors::length` error

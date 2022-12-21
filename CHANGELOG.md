# Changelog: Cli

> version: $Id$ ($Date$)

## History

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

# Changelog: Cli

> version: $Id$ ($Date$)

## History

### develop: 0.14.0 (2022 Dec 15)

 - new: `Pencil::format` returns colour string without outputting it
 - new: `Pencil::input` reads terminal input
 - new: `Pencil` cache original text for reversing it in certain situations
 - new: `Colour::Default` system colour
 - fix: `Arguments::getOption` return type
 - update: `Arguments` implements `\Inane\Stdlib\Converters\JSONable`
 - update: `Arguments::toJSON` added $pretty option to format json
 - update: `HelpScreen` fixes for *php 8.1*
 - fix: various little updates and fixes

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

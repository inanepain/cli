<?php

use \Inane\Cli\Pencil;
use \Inane\Cli\Pencil\Colour;
use \Inane\Cli\Pencil\Style;

require_once 'common.php';

/*
Pencils can be use by method or echo like functions or mixing both.
*/

#region Pencils
$default = new Pencil(Colour::Default);
$defaultBlink = new Pencil(Colour::Default, Style::SlowBlink);
$green = new Pencil(Colour::Green);
$greenBlink = new Pencil(Colour::Green, Style::SlowBlink);
$greenBlinkBGRed = new Pencil(Colour::Green, Style::SlowBlink, Colour::Red);
$cyanBold = new Pencil(Colour::Cyan, Style::Bold);
$blueUnderline = new Pencil(Colour::Blue, Style::Underline);
#endregion Pencils

$green->line('');
// Using the methods to write `reset` it defaults to reset at the end of text.
$green->line('Green');
$default->line('');
$greenBlink->line('Green');
// Using echo, reset must be manually called.
//echo "{$green}green", Pencil::reset(), "\n";
$default->line('Default');
$defaultBlink->line('Default');
$green->out('green ');
$greenBlinkBGRed->line('blink green with bg-red');
//echo "{$green}green {$greenBlinkBGRed}bg-green", Pencil::reset(), "\n";
$default->line('');
$default->line('Default');
// Here a pencil method is used and another pencil added to the string to underline that section.
$cyanBold->line("Name: {$blueUnderline}Pencil");

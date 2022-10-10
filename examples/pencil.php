<?php

use \Inane\Cli\Pencil;
use \Inane\Cli\Pencil\Colour;
use \Inane\Cli\Pencil\Style;

require_once 'common.php';

/*
Pencils can be use by method or echo like functions or mixing both.
*/

$g = new Pencil(Colour::Green, style: Style::SlowBlink);
$gbg = new Pencil(colour: Colour::Green, background: Colour::Red, style: Style::SlowBlink);

// Using the methods to write `reset` it defaults to reset at the end of text.
$g->line('Green');
// Using echo, reset must be manually called.
echo "{$g}green", Pencil::reset(), "\n";

$g->out('green ');
$gbg->line('bg-green');
echo "{$g}green {$gbg}bg-green", Pencil::reset(), "\n";

$r = new Pencil(style: Style::Bold);
$bu = new Pencil(Colour::Blue, style: Style::Underline);
// Here a pencil method is used and another pencil added to the string to underline that section.
$r->line("Name: {$bu}Pencil");

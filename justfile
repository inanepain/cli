# inanepain/datetime
# version: $Id$
# date: $Date$

set shell := ["zsh", "-cu"]
set positional-arguments

project := "inane\\cli"

# list recipes
_default:
    @echo "{{project}}:"
    @just --list --list-heading ''

# generate php doc
@doc:
	mkdir -p doc/code
	phpdoc -d src -t doc/code --title="{{project}}" --defaultpackagename="Inane"

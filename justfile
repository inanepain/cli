# inanepain/cli
# version: $Id$
# date: $Date$

set shell := ["zsh", "-cu"]
set positional-arguments

project := "inane\\cli"

# list recipes
_default:
    @echo "{{project}}:"
    @just --list --list-heading ''

# generate php part
@doc:
	mkdir -p part/code
	phpdoc -d src -t part/code --title="{{project}}" --defaultpackagename="Inane"

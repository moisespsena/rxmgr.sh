#!/bin/bash
source $(dirname $(realpath "$0"))"/../env/bin/activate" || exit 1
python "$@"
exit $?

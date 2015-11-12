#!/bin/bash
cd $(dirname $(realpath "$0"))
./python.sh server.py
exit $?

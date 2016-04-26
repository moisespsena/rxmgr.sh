#!/bin/bash
D=$(dirname $(dirname $(realpath "$0")))
cd ~ || exit $?

if [ ! -e ".vnc/.rxmgr" ]; then 
  mv ".vnc/xstartup" "$HOME/.vnc/.rxmgr" || touch ".vnc/.rxmgr" || exit $?
  cp "$D/templates/xstartup" ~/.vnc
fi

exit $?

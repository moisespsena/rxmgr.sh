#!/bin/bash

KEY="$1"
PID=$$
TMPDP="/tmp/rxmgr"
TMPD="$TMPDP/$PID"
TMPFO="$TMPD/out"
TMPFE="$TMPD/error"
TMPFS="$TMPD/status"
TMPFK="$TMPD/kill"
TIMEOUT=3
SUBPID=


if [ "$2" = '' ]; then
    echo "ERROR: Invalid input file. ARGV[2] is empty" >&2
    exit 1
fi

INPUT_FILE="$2"

function lg_clean {
	cat /dev/null > "$TMPFO"
	cat /dev/null > "$TMPFE"
	cat /dev/null > "$TMPFS"
	cat /dev/null > "$TMPFK"
}

function on_kill {
    [ "$SUBPID" != '' ] && kill -9 "$SUBPID"
	rm -rf "$TMPD"
}


test_sub () {
   ps ax --format "pid" | grep "^$SUBPID$" >/dev/null && return 0
   return 1
}

function gen_out_f {
    echo "-- $KEY:$1"
	cat "$2"
}


function gen_out {
	gen_out_f out "$TMPFO"
	gen_out_f err "$TMPFE"
	gen_out_f status "$TMPFS"
	gen_out_f killed "$TMPFK"
}


rm -rf "$TMPDP"
mkdir -p "$TMPD"

lg_clean

trap on_kill SIGHUP SIGINT SIGTERM

CMD="
function on_subkill {
	echo killed on \`date\` > '$TMPFK'
}

trap on_subkill SIGHUP SIGINT SIGTERM

( "$(cat "$INPUT_FILE")" )>'$TMPFO' 2>'$TMPFE'
EC=\$?
echo \$EC > '$TMPFS'
exit \$EC
"

function _run() {
	echo -e "$CMD" | bash &
	SUBPID=$!

	i=1
	while true; do
		test_sub || break
	
		if [ $i -gt $TIMEOUT ]; then
			echo killing "$SUBPID"
			date > "$TMPFK"
			kill -9 "$SUBPID"
			break
		fi

		i=$((i+1))
		sleep 1
	done
}

_run
gen_out
lg_clean

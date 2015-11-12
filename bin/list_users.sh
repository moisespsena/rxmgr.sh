#!/bin/bash
awk -F: '$2 != "*" && $2 !~ /^!/ { print $1 }' /etc/shadow | \
    grep -v root | \
    while read username; do
        homedir=$(getent passwd "$username" | cut -d: -f6)
        [ "$homedir" != '' ] && echo "$username:$homedir"
    done


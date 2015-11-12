user="$1"
password="$2"

shadow_line=$(grep "$user" /etc/shadow | awk -F':' '{ print $2 }')
[ "$shadow_line" = '' ] && {
    echo "Access danied for user '$user'." >&2
    exit 2
}
method=$(echo "$shadow_line" | cut -d'$' -f 2)
salt=$(echo "$shadow_line" | cut -d'$' -f 3)
epwd=$(echo "$shadow_line" | cut -d'$' -f 4)

case $method in
    1) method="MD5" ;;
    5) method="SHA-256" ;;
    6) method="SHA-512" ;;
    *) echo "Invalid crypt method '$method' for password of '$user'" >&2
       exit 2
       ;;
esac

mkpasswd_result=$(mkpasswd -m "$method" -S "$salt" "$password") || exit 2
mkepwd=$(echo "$mkpasswd_result" | cut -d'$' -f 4)

[ "$mkepwd" = "$epwd" ] && exit 0 || exit 1

#!/bin/bash

if [ "$USER" != 'root' ]; then
   sudo "$0"
   S=$?
   if [ $S -gt 0 ]; then
       echo cancelado. >&2
   fi
   exit $S
fi

H="/home/rxmgr"
U="rxmgr"
PH="$H/public_html"
B="$H/bin"
BK="$H/old/"`date +'%Y/%m/%d/%H%M%S'`

grep ^rxmgr /etc/passwd >/dev/null || {
    adduser --system --home "$H" --disabled-password "$U" --shell /bin/bash
    addgroup rxmgr
    usermod -g rxmgr rxmgr
}
function _backup() {
    [ ! -e "$BK" ] && mkdir -pv "$BK"
    mv -v "$1" "$BK"
}

function _copy() {
    [ ! -e "$O" ] && mkdiv -v "$O"
    [ -e "$B" ] && _backup "$B"
    [ -e "$PH" ] && _backup "$PH"

    cp -r --preserve=all ./* "$H"
    cp -r --preserve=all ./.git "$H"
    (cd "$H" && git reset --hard)
    chown -R rxmgr.rxmgr "$H"
}

apt-get update || exit 1
apt-get install nginx php5-fpm acl autocutsel git lxde-common tightvncserver xtightvncviewer cifs-utils -y || exit 1

which pure-pw >/dev/null || {
    apt-get install pure-ftpd
    echo "yes" > /etc/pure-ftpd/conf/ChrootEveryone
    echo "yes" > /etc/pure-ftpd/conf/KeepAllFiles
    echo "30000 30200" > /etc/pure-ftpd/conf/PassivePortRange
}

(
echo "Configure VNC xstartup"

"$B/list_users.sh" | while read l; do
   user=$(echo "$l" | cut -d: -f 1)
   home=$(echo "$l" | cut -d: -f 2)
   echo "--> $home"
   [ ! -d "$home/.vnc" ] && mkdir -pv "$home/.vnc"
   echo '#!/bin/sh
xrdb $HOME/.Xresources
xsetroot -solid grey
# Fix to make GNOME work
export XKL_XMODMAP_DISABLE=1
autocutsel -fork
# inicia o lxde como gerenciador de sessão
startlxde
' > "$home/.vnc/xstartup"
   chmod +x "$home/.vnc/xstartup"
   chown -R "$user.$user" $home/.vnc
done
)

echo "copiando arquivos..."

_copy

echo 'stop rxmgr' | supervisorctl

cat > /etc/supervisor/conf.d/rxmgr.conf <<HERE
[program:rxmgr]
command="$B/rxmgr_server.sh"
autostart=true
autorestart=true
stdout_logfile=/var/log/rxmgr.log
stdout_logfile_maxbytes=2MB
stdout_logfile_backups=5
stdout_capture_maxbytes=2MB
stderr_logfile=/var/log/rxmgr.err.log
stderr_logfile_maxbytes=2MB
stderr_logfile_backups=5
stderr_capture_maxbytes=2MB
user=root
HERE

cat <(
cat <<HERE
[ ! -e env ] && {
    echo "Removing python virtual env in "\`pwd\`"/env"
    rm -rf env
}

[ ! -e env ] && {
    virtualenv --system-site-packages -p \`which python2\` env
}


cat > bin/python.sh <<CMD
#!/bin/bash
source \\\$(dirname \\\$(realpath "\\\$0"))"/../env/bin/activate" || exit 1
python "\\\$@"
exit \\\$?
CMD

chmod +x bin/python.sh

cat > bin/rxmgr_server.sh <<CMD
#!/bin/bash
cd \\\$(dirname \\\$(realpath "\\\$0"))
./python.sh server.py
exit \\\$?
CMD

chmod +x bin/rxmgr_server.sh
HERE
) | su - rxmgr -c 'cat | bash'

echo update | supervisorctl
echo 'start rxmgr' | supervisorctl

setfacl -m u:rxmgr:r /etc/shadow

cat > /etc/nginx/sites-available/rxmgr.conf <<'CMD'
server {
    listen 3000 default_server;
    listen [::]:3000 default_server;
    client_max_body_size 50M;

    root /home/rxmgr/public_html;
    index index.php index.html index.htm;

    location / {
            try_files $uri $uri/ /index.php;
    }

    location ~ \.php$ {
            try_files $uri =404;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
            fastcgi_index index.php;
            fastcgi_pass unix:/var/run/php5-fpm-rxmgr.sock;
    }
}
CMD

cat > /etc/php5/fpm/pool.d/rxmgr.conf <<CMD
[rxmgr]
user = rxmgr
group = rxmgr
listen = /var/run/php5-fpm-rxmgr.sock
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
chdir = /
CMD

rm -fv /etc/nginx/sites-enabled/rxmgr
ln -vs /etc/nginx/sites-available/rxmgr.conf /etc/nginx/sites-enabled/rxmgr

service php5-fpm restart
service nginx restart
echo installed successful!
echo done.

import sys
import re
import subprocess
import json
import os
import shutil
from collections import OrderedDict as OD
import time


DEBUG = True


class str_lines(str):
    def lines(self):
        return self.strip().split('\n')


def ple(msg):
    sys.stderr.write("[ERRO]: %s\n" % msg)


class CmdResult(dict):
    def __init__(self, cmd, status, out, err):
        super(CmdResult, self).__init__()
        self['status'] = status
        self['out'] = out
        self['err'] = err
        self.cmd = cmd

    @property
    def status(self):
        return self['status']

    @property
    def out(self):
        return self['out']

    def outlines(self):
        return self.out.strip().split('\n')

    @property
    def err(self):
        return self['err']

    def check(self):
        if self.status != 0:
            sys.stderr.write(self.err)
            exit(self.status)
        return self


def run(cmd='', username=None, usercmd='', pre=None, pos=None, upre=None, upos=None):
    if DEBUG:
        debug("RUN: %r" % cmd)
    _cmd = []
    p = subprocess.Popen('cat | bash', stdin=subprocess.PIPE,
                         stdout=subprocess.PIPE, shell=True,
                         stderr=subprocess.PIPE)

    def c(cmd):
        _cmd.append(cmd)
        p.stdin.write(cmd)
        if DEBUG:
            debug("    RUN: %r" % cmd)

    c('cd ~\n')
    c('export HOME=$(pwd)\n')
    c(cmd)
    c('\n')

    if pre:
        pre(p, c)

    if username:
        c("su - '%s' -c 'cat | bash'\n" % username)
        c('cd ~\n')
        c('export HOME=$(pwd)\n')

        if upre:
            upre(p, c)

        c(usercmd)
        c('\n')

        if upos:
            upos(p, c)

        c('exit\n')

    if pos:
        pos(p, c)

    p.stdin.close()

    p.wait()
    r = CmdResult(''.join(_cmd), p.returncode, p.stdout.read(),
                  p.stderr.read())
    if DEBUG:
        debug("    RUN RESULT: %r" % r.out)
    return r


def user_call(username, password, cmd, **kwargs):
    return run(username=username, usercmd=cmd, **kwargs)


root_call = run


class Manager(object):
    def __init__(self):
        self.p = os.path.join(os.path.dirname(os.path.dirname(__file__)), 'data')

    def add_user(self, user, password):
        up = os.path.join(self.p, user)
        os.makedirs(up)
        with open(os.path.join(up, 'password.txt'), mode='w') as f:
            f.write(password)

    def set_user_password(self, user, password):
        up = os.path.join(self.p, user)
        if not os.path.exists(up):
            os.makedirs(up)
        with open(os.path.join(up, 'password.txt'), mode='w') as f:
            f.write(password)

    def get_user_password(self, user):
        up = os.path.join(self.p, user, 'password.txt')
        return open(up).read() if os.path.exists(up) else ''

    def remove_user(self, user):
        up = os.path.join(self.p, user)
        shutil.rmtree(up)

    def exists_user(self, user):
        #up = os.path.join(self.p, user)
        #return os.path.exists(up)
        return user in self.list_system_users()

    def add_user_display(self, user):
        dp = os.path.join(self.p, user, 'displays.txt')
        qnt = 0
        if os.path.exists(dp):
            qnt = int(open(dp).read())
        qnt += 1
        with open(dp, mode='w') as f:
            f.write(str(qnt))

    def remove_user_display(self, user):
        dp = os.path.join(self.p, user, 'displays.txt')
        if os.path.exists(dp):
            qnt = int(open(dp).read())
            qnt -= 1
            with open(dp, mode='w') as f:
                f.write(str(qnt))
            return True
        return False

    def get_user_displays(self, user):
        dp = os.path.join(self.p, user, 'displays.txt')
        if os.path.exists(dp):
            qnt = int(open(dp).read())
            return qnt
        return 0

    def list_system_users(self):
        cmd = ("""awk -F: '$2 != "*" && $2 !~ /^!/ { print $1 }' /etc/shadow | """
              """grep -v root""")
        r = root_call(cmd)
        return r.out.strip('\n').split('\n')

    def list_users(self, password=False):
        r = []
        for user in self.list_system_users():
            if password:
                psp = os.path.join(self.p, user, 'password.txt')
                if os.path.exists(psp):
                    r.append((user, open(psp).read()))
            else:
                r.append(user)
        return r

    def system_check_password(self, user, password):
        r = root_call("%s/passwdcheck.sh '%s' '%s'" % (os.path.dirname(__file__), user, password))
        return r.status == 0


M = Manager()


def debug(data):
    sys.stderr.write("DEBUG: %s\n" % data)


def parseCmd(args):
    d = {}
    d['cmd_'] = args[0]

    args = args[1:]
    l = len(args)
    i = 0

    while i < l:
        arg = args[i]
        if arg[0] == ':':
            d['display_'] = arg
            d['ID'] = int(arg[1:])
        elif arg[0] == '-':
            d[arg[1:]] = args[i + 1]
            i += 1
        i += 1

    return d


def parseProc(args):
    d = {}
    d['user'] = args[0]
    d['pid'] = args[1]
    return d


def parseProcLine(l):
    l = l.rstrip()
    if l:
        proc, cmd = l.split('Xtightvnc')
        cmd = parseCmd(re.split(r'\s+', 'Xtightvnc' + cmd))
        proc = parseProc(re.split(r'\s+', proc))
        return [proc, cmd]
    return None


def list_displays_cmd(user=None):
    cmd = 'ps aux | '
    if user:
        cmd += "grep '^%s' |" % user
    cmd += 'grep Xtightvnc | grep -v grep'
    return cmd


def list_displays(user=None, ret_=False):
    cmd = list_displays_cmd(user=user)
    rcmd = root_call(cmd)

    if user:
        r = []
        if M.exists_user(user):
            for l in rcmd.outlines():
                if l:
                    pl = parseProcLine(l)
                    r.append(pl)
        else:
            sys.stderr.write("User '%s' not exists" % user)
            exit(1)
        r = sorted(r, key=lambda l: int(l[1]['display_'][1:]))
    else:
        r = OD([(user, []) for user in sorted(M.list_users())])
        for l in rcmd.outlines():
            pl = parseProcLine(l)

            if pl and pl[0]['user'] in r:
                r[pl[0]['user']].append(pl)
        for k in r:
            r[k] = sorted(r[k], key=lambda l: int(l[1]['display_'][1:]))
    if ret_:
        return r

    print(json.dumps(r))


def create_display(user, args='', password=None):

    if password is not None:
        c = check_password(user, password)
        if c:
            return c

    m = set()

    for d in list_displays(user=user, ret_=True):
        m.add(d[1]['display_'])

    if not password:
        password = M.get_user_password(user)

    user_call(user, password, 'vncserver %s' % args).check()

    display = None

    for d in list_displays(user=user, ret_=True):
        if not d[1]['display_'] in m:
            display = d

    if display:
        configure_guacamole()
        print(json.dumps(display))


def close_display(user, display, password=None):
    if display[0] != ':':
        display = ':%s' % display

    if password is not None:
        c = check_password(user, password)
        if c:
            return c

    if not password:
        password = M.get_user_password(user)

    user_call(user, password, 'vncserver -kill %s' % display).check()

    configure_guacamole()


def set_password(user, password):
    if not M.exists_user(user):
        ple("Usuario '%s' nao registrado." % user)

    root_call('echo -e "%s\n%s" | passwd -q "%s"' % (password, password, user))
    user_call(user, password,
              'echo "%s\n%s" | vncpasswd' % (password, password))
    M.set_user_password(user, password)
    configure_guacamole()


def configure_guacamole():
    d = ['<user-mapping>']

    for user, password in M.list_users(password=True):
        d.append(' <authorize username="%s" password="%s">' % (user, password))
        displays = list_displays(user=user, ret_=True)
        for proc, cmd in displays:
            d.append('    <connection name="display_%s">' % cmd['ID'])
            d.append('      <protocol>vnc</protocol>')
            d.append('      <param name="hostname">localhost</param>')
            d.append('      <param name="port">%s</param>' % cmd['rfbport'])
            d.append('      <param name="password">%s</param>' % password)
            d.append('    </connection>')
        d.append('    <connection name="ssh">')
        d.append('      <protocol>ssh</protocol>')
        d.append('      <param name="hostname">localhost</param>')
        d.append('      <param name="port">22</param>')
        d.append('      <param name="user">%s</param>' % user)
        d.append('      <param name="password">%s</param>' % password)
        d.append('      <param name="enable-sftp">true</param>')
        d.append('      <param name="font-size">9</param>')
        d.append('    </connection>')
        d.append('  </authorize>')
    d.append('</user-mapping>')

    def pre(p, c):
        p.stdin.write('\n'.join(d) + '\n')

    root_call('cat > /etc/guacamole/user-mapping.xml', pre=pre)


def add_user(username, password):
    if M.exists_user(username):
        ple("Usuario '%s' ja esta registrado." % username)
        return

    M.add_user(username, password)
    set_password(username, password)


def check_password(username, password):
    if M.system_check_password(username, password):
        if not M.exists_user(username):
            add_user(username, password)

        p2 = M.get_user_password(username)

        if p2 != password:
            set_password(username, password)

    else:
        ple("Senha incorreta.")
        return 1

def timeout(t = 1):
    time.sleep(t)


if __name__ == '__main__':
    if len(sys.argv) == 3 and sys.argv[1] == 'exec':
        code = eval(sys.argv[2])
        if code is None:
            code = 0
        exit(code)
    if len(sys.argv) >= 2 and sys.argv[1] == '-':
        if len(sys.argv) == 2:
            istream = sys.stdin
        else:
            istream = open(sys.argv[2])
        code = istream.read()
        exec code
    else:
        print(sys.argv)

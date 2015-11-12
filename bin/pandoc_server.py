#!/usr/bin/env python

import SocketServer
import json
import os
import sys
import signal
import threading
import traceback
from cStringIO import StringIO
import thread

import manager


__author__ = 'moi'

HOST = ''  # Symbolic name meaning all available interfaces
PORT = 30000  # Arbitrary non-privileged port


def read_line(conn):
    "read a line from a socket"
    chars = []
    while True:
        a = conn.recv(1)
        chars.append(a)
        if a == "\n" or a == "":
            return "".join(chars).replace('\r', '').strip()


def recv(conn, l):
    c = []
    q = 0
    while q < l:
        cc = conn.recv(1)
        if cc != '\r':
            c.append(cc)
            q += 1
    return ''.join(c)


def on_connect(conn):
    line = read_line(conn)

    if line.isdigit():
        data = conn.recv(int(line))
        r = run_command(data)
        conn.sendall(repr(r))
    else:
        conn.sendall('Invalid LINE: %r' % line)
    # came out of loop
    conn.close()


KEY_PATH = os.path.join(os.path.dirname(__file__), 'key')


def get_gen_key():
    v = open(KEY_PATH).read()
    # nv = uuid.uuid4()
    # nv = str(nv)
    # open(KEY_PATH, mode='w').write(nv)
    return v


class MyServer(SocketServer.UnixStreamServer):
    allow_reuse_address = True


class MyTCPHandler(SocketServer.BaseRequestHandler):
    """
    The RequestHandler class for our server.

    It is instantiated once per connection to the server, and must
    override the handle() method to implement communication to the
    client.
    """

    def handle(self):
        # self.request is the TCP socket connected to the client
        print("%s connected" % self.client_address)
        conn = self.request
        line = read_line(conn)

        def se(msg):
            conn.sendall(json.dumps({"server_error": msg}) + '\n')

        if line.isdigit():
            data = recv(conn, int(line))
            data = data.replace('\r', '')
            pos = data.find('\n')
            if pos > 0:
                key = data[:pos]
                data = data[pos + 1:]
                rkey = get_gen_key()
                if rkey:
                    if key == rkey:
                        r = [None]
                        rt = timeout(run_command,args=(data, r), duration=10)
                        if rt:
                            r = rt
                        else:
                            r = r[0]
                        conn.sendall(json.dumps(r))
                    else:
                        se("invalid key")
                else:
                    conn.sendall('{"server_retry":true}\n')
            else:
                se("no line with data found")
        else:
            se("Invalid LINE: %s" % line)


def start_server():
    path = 'sock.sock'
    # Create the server, binding to localhost on port 9999
    if os.path.exists(path):
        os.unlink(path)
    server = [MyServer(path, MyTCPHandler)]
    print('Socket listening on  %s' % path)

    def receive_signal(signum, stack):
        if signum in [1, 2, 3, 15]:
            server[0] = None
            #print 'Caught signal %s, exiting.' % (str(signum))
            sys.exit()
        else:
            #print 'Caught signal %s, ignoring.' % (str(signum))
            pass

    uncatchable = ['SIG_DFL', 'SIGSTOP', 'SIGKILL']
    for i in [x for x in dir(signal) if x.startswith("SIG")]:
        if not i in uncatchable:
            signum = getattr(signal, i)
            signal.signal(signum, receive_signal)

    # Activate the server; this will keep running until you
    # interrupt the program with Ctrl-C
    os.chmod(path, 0x777)
    server[0].serve_forever()


def timeout(func, args=(), kwargs={}, duration=10):
    it = threading.Thread(target=func, args=args, kwargs=kwargs)
    it.start()
    it.join(duration)
    if it.isAlive():
        return {'status': 1024, 'err': 'Timeout error', 'out': ''}
    else:
        return None


def run_command(cmd, r):
    o = sys.stdout
    e = sys.stderr

    sys.stdout = no = StringIO()
    sys.stderr = ne = StringIO()
    status_code = [0]

    def status(code):
        if not code is None:
            status_code[0] = code

    cmd = 'status(m.%s)' % cmd
    error = False

    try:
        output = subprocess.check_output(cmd);
    except e:
        error = True
        traceback.print_exc()
        status_code[0] = 127
    finally:
        sys.stdout = o
        sys.stderr = e

    if error:
        traceback.print_exc()

    r[0] = {'status': status_code[0], 'out': no.getvalue(), 'err': ne.getvalue()}


start_server()
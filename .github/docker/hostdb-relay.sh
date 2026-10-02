#!/bin/sh
# Database hosts on the machine itself. A Pterodactyl panel that was upgraded to Recoded Ptero may
# have database hosts entered as 127.0.0.1 or localhost (MySQL on the same server, usually only
# listening there). Inside the panel container those addresses mean the container, so they are
# forwarded through a socket in a shared volume, without opening MySQL to any network:
#
#   panel container  127.0.0.1:<port>  ->  /run/hostdb/tcp-<ip>-<port>.sock  ->  hostdb service  ->  <ip>:<port> on the host
#   panel container  localhost (PHP's default socket /run/hostdb/mysqld.sock)  ->  host's MySQL socket
#
# MySQL still sees 127.0.0.1 or its own socket, so its users and grants keep working unchanged.
#   hostdb-relay.sh host    runs in the hostdb service (host network)
#   hostdb-relay.sh panel   runs in the panel container (supervisord)
# HOSTDB_TARGETS: comma separated <ip>:<port> (loopback addresses only), HOSTDB_SOCKET_NAME: the
# host's MySQL socket file, mounted at /run/host-mysqld.

mode="$1"
mkdir -p /run/hostdb
started=0

for target in $(echo "${HOSTDB_TARGETS:-}" | tr ',' ' '); do
    ip="${target%:*}"
    port="${target##*:}"
    # Only loopback addresses and real port numbers; anything else is ignored.
    case "$ip" in 127.*) ;; *) continue ;; esac
    echo "$ip" | grep -Eq '^127\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$' || continue
    echo "$port" | grep -Eq '^[0-9]{1,5}$' || continue
    sock="/run/hostdb/tcp-$ip-$port.sock"
    if [ "$mode" = "host" ]; then
        socat "UNIX-LISTEN:$sock,fork,unlink-early,mode=666" "TCP:$ip:$port" &
    else
        socat "TCP-LISTEN:$port,bind=$ip,fork,reuseaddr" "UNIX-CONNECT:$sock" &
    fi
    started=1
done

if [ "$mode" = "host" ] && [ -n "${HOSTDB_SOCKET_NAME:-}" ] && [ -S "/run/host-mysqld/$HOSTDB_SOCKET_NAME" ]; then
    socat "UNIX-LISTEN:/run/hostdb/mysqld.sock,fork,unlink-early,mode=666" "UNIX-CONNECT:/run/host-mysqld/$HOSTDB_SOCKET_NAME" &
    started=1
fi

if [ "$started" = "0" ]; then
    # Nothing to forward (the normal case): the hostdb service idles, supervisord leaves it stopped.
    [ "$mode" = "host" ] && exec sleep 2147483647
    exit 0
fi
wait

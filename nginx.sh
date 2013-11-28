#!/bin/bash

#

# Startup script for the nginx

#


start() {
    /usr/local/sbin/nginx
}



stop() {
    kill `cat /var/run/nginx.pid`
}


case "$1" in

    start)

        start

        ;;

    stop)

        stop

        ;;

    restart)

        stop

        start

        ;;

    *)

        echo "Usage: $0 {start|stop|restart}"

esac

exit 0


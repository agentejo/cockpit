#!/bin/sh

if [ "${TRAVIS_OS_NAME}" = "osx" ]; then
    # https://www.ics.uci.edu/~pattis/common/handouts/macmingweclipse/allexperimental/mac-gdb-install.html
    echo "Cannot debug core files on macOS: ${1}"
    exit 1
fi

PHP_BINARY=`which php`
gdb -batch -ex "bt full" -ex "quit" "${PHP_BINARY}" "${1}"

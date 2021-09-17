#!/bin/sh

if [ "$#" -ne 1 ]; then
  echo "$0 requires one argument: <MONGO_ORCHESTRATION_HOME>"
  echo "For example: $0 /tmp/mongo-orchestration-home"
  exit 1
fi

set -o xtrace   # Write all commands first to stderr
set -o errexit  # Exit the script with error if any of the commands fail


MONGO_ORCHESTRATION_HOME="$1"

echo From shell `date` > $MONGO_ORCHESTRATION_HOME/server.log

cd "$MONGO_ORCHESTRATION_HOME"
# Setup or use the existing virtualenv for mongo-orchestration.
# Prefer using Python 3 from the toolchain over the default system python.
# We may still fallback to using an old version of Python here.
# Many of the Linux distros in Evergreen ship Python 2.6 as the
# system Python. Core libraries installed by virtualenv (setuptools,
# pip, wheel) have dropped, or soon will drop, support for Python
# 2.6. Starting with version 14, virtualenv upgrades these libraries
# to the latest available on pypi when creating the virtual environment
# unless you pass --never-download.
PYTHON=$(command -v /opt/mongodbtoolchain/v2/bin/python3 || command -v python3 || command -v python)
$PYTHON -c 'import sys; print(sys.version)'
$PYTHON -c 'import ssl; print(ssl.OPENSSL_VERSION)'

if [ -f venv/bin/activate ]; then
  . venv/bin/activate
elif [ -f venv/Scripts/activate ]; then
  . venv/Scripts/activate
elif $PYTHON -m virtualenv --system-site-packages --never-download venv || virtualenv --system-site-packages --never-download venv; then
  if [ -f venv/bin/activate ]; then
    . venv/bin/activate
  elif [ -f venv/Scripts/activate ]; then
    . venv/Scripts/activate
  fi
  # Install from github to get the latest mongo-orchestration.
  pip install --upgrade 'git+git://github.com/mongodb/mongo-orchestration@master'
  pip list
fi
cd -

ORCHESTRATION_ARGUMENTS="-e default -f $MONGO_ORCHESTRATION_HOME/orchestration.config --socket-timeout-ms=60000 --bind=127.0.0.1 --enable-majority-read-concern"
if [ "Windows_NT" = "$OS" ]; then # Magic variable in cygwin
  ORCHESTRATION_ARGUMENTS="$ORCHESTRATION_ARGUMENTS -s wsgiref"
fi

# Forcibly kill the process listening on port 8889, most likey a wild
# mongo-orchestration left running from a previous task.
if [ "Windows_NT" = "$OS" ]; then # Magic variable in cygwin
  OLD_MO_PID=$(netstat -ano | grep ':8889 .* LISTENING' | awk '{print $5}' | tr -d '[:space:]')
  if [ ! -z "$OLD_MO_PID" ]; then
    taskkill /F /T /PID "$OLD_MO_PID" || true
  fi
else
  OLD_MO_PID=$(lsof -t -i:8889 || true)
  if [ ! -z "$OLD_MO_PID" ]; then
    kill -9 "$OLD_MO_PID" || true
  fi
fi

mongo-orchestration $ORCHESTRATION_ARGUMENTS start > $MONGO_ORCHESTRATION_HOME/out.log 2>&1 < /dev/null &

ls -la $MONGO_ORCHESTRATION_HOME

sleep 5
if ! curl http://localhost:8889/ --silent --show-error --max-time 120 --fail; then
  echo Failed to start mongo-orchestration, see $MONGO_ORCHESTRATION_HOME/out.log:
  cat $MONGO_ORCHESTRATION_HOME/out.log
  echo Failed to start mongo-orchestration, see $MONGO_ORCHESTRATION_HOME/server.log:
  cat $MONGO_ORCHESTRATION_HOME/server.log
  exit 1
fi
sleep 5

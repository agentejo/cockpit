#!/bin/sh
set -o xtrace   # Write all commands first to stderr
set -o errexit  # Exit the script with error if any of the commands fail

cd "$MONGO_ORCHESTRATION_HOME"
# source the mongo-orchestration virtualenv if it exists
if [ -f venv/bin/activate ]; then
    . venv/bin/activate
elif [ -f venv/Scripts/activate ]; then
    . venv/Scripts/activate
fi
mongo-orchestration stop

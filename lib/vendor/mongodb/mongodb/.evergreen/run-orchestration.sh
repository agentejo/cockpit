#!/bin/sh
set -o xtrace   # Write all commands first to stderr
set -o errexit  # Exit the script with error if any of the commands fail


AUTH=${AUTH:-noauth}
SSL=${SSL:-nossl}
TOPOLOGY=${TOPOLOGY:-server}
STORAGE_ENGINE=${STORAGE_ENGINE}
# Set to a non-empty string to use the <topology>/disableTestCommands.json
# cluster config, eg DISABLE_TEST_COMMANDS=1
DISABLE_TEST_COMMANDS=${DISABLE_TEST_COMMANDS}
MONGODB_VERSION=${MONGODB_VERSION:-latest}

DL_START=$(date +%s)
DIR=$(dirname $0)
# Functions to fetch MongoDB binaries
. $DIR/download-mongodb.sh

get_distro
if [ -z "$MONGODB_DOWNLOAD_URL" ]; then
    get_mongodb_download_url_for "$DISTRO" "$MONGODB_VERSION"
else
  # Even though we have the MONGODB_DOWNLOAD_URL, we still call this to get the proper EXTRACT variable
  get_mongodb_download_url_for "$DISTRO"
fi
download_and_extract "$MONGODB_DOWNLOAD_URL" "$EXTRACT"

DL_END=$(date +%s)
MO_START=$(date +%s)

ORCHESTRATION_FILE=${ORCHESTRATION_FILE}
# If no orchestration file was specified, build up the name based on configuration parameters.
if [ -z "$ORCHESTRATION_FILE" ]; then
  ORCHESTRATION_FILE="basic"
  if [ "$AUTH" = "auth" ]; then
    ORCHESTRATION_FILE="auth"
  fi

  if [ "$SSL" != "nossl" ]; then
    ORCHESTRATION_FILE="${ORCHESTRATION_FILE}-ssl"
  fi

  # disableTestCommands files do not exist for different auth or ssl modes.
  if [ ! -z "$DISABLE_TEST_COMMANDS" ]; then
    ORCHESTRATION_FILE="disableTestCommands"
  fi

  # Storage engine config files do not exist for different auth or ssl modes.
  if [ ! -z "$STORAGE_ENGINE" ]; then
    ORCHESTRATION_FILE="$STORAGE_ENGINE"
  fi

  ORCHESTRATION_FILE="${ORCHESTRATION_FILE}.json"
fi

TOOLS_ORCHESTRATION_FILE="$MONGO_ORCHESTRATION_HOME/configs/${TOPOLOGY}s/${ORCHESTRATION_FILE}"
CUSTOM_ORCHESTRATION_FILE="$DIR/orchestration/configs/${TOPOLOGY}s/${ORCHESTRATION_FILE}"

if [ -f "$CUSTOM_ORCHESTRATION_FILE" ]; then
  export ORCHESTRATION_FILE="$CUSTOM_ORCHESTRATION_FILE"
elif [ -f "$TOOLS_ORCHESTRATION_FILE" ]; then
  export ORCHESTRATION_FILE="$TOOLS_ORCHESTRATION_FILE"
else
  echo "Could not find orchestration file $ORCHESTRATION_FILE"
  exit 1
fi

export ORCHESTRATION_URL="http://localhost:8889/v1/${TOPOLOGY}s"

# Start mongo-orchestration
sh $DIR/start-orchestration.sh "$MONGO_ORCHESTRATION_HOME"

pwd
if ! curl --silent --show-error --data @"$ORCHESTRATION_FILE" "$ORCHESTRATION_URL" --max-time 600 --fail -o tmp.json; then
  echo Failed to start cluster, see $MONGO_ORCHESTRATION_HOME/out.log:
  cat $MONGO_ORCHESTRATION_HOME/out.log
  echo Failed to start cluster, see $MONGO_ORCHESTRATION_HOME/server.log:
  cat $MONGO_ORCHESTRATION_HOME/server.log
  exit 1
fi
cat tmp.json
URI=$(python -c 'import sys, json; j=json.load(open("tmp.json")); print(j["mongodb_auth_uri" if "mongodb_auth_uri" in j else "mongodb_uri"])' | tr -d '\r')
echo 'MONGODB_URI: "'$URI'"' > mo-expansion.yml
echo "Cluster URI: $URI"

MO_END=$(date +%s)
MO_ELAPSED=$(expr $MO_END - $MO_START)
DL_ELAPSED=$(expr $DL_END - $DL_START)
cat <<EOT >> $DRIVERS_TOOLS/results.json
{"results": [
  {
    "status": "PASS",
    "test_file": "Orchestration",
    "start": $MO_START,
    "end": $MO_END,
    "elapsed": $MO_ELAPSED
  },
  {
    "status": "PASS",
    "test_file": "Download MongoDB",
    "start": $DL_START,
    "end": $DL_END,
    "elapsed": $DL_ELAPSED
  }
]}

EOT

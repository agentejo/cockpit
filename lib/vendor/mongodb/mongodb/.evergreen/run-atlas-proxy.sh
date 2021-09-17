#!/bin/sh
#
# This script uses the Atlas proxy project's own evergreen launch script
# to build and launch an Atlas proxy for testing.  It works directly from
# their master branch, so may fail if they break something.
#
# There is no corresponding 'shutdown' script; the Atlas proxy project
# relies on Evergreen to terminate processes and clean up when tasks end,
# so we do the same.
#
# The URI is harded coded as:
# mongodb://user:pencil@host5.local.10gen.cc:9900/admin?replicaSet=benchmark
#
# Connections requires SSL and the CA file is 'main/ca.pem' in the atlasproxy repo.
#
# If this fails, check the 'main/test.sh' file in the atlasproxy repo for
# possible changes.
#
# Installation of Go dependencies appears to require a newer git.  1.7 on
# rhel62 failed, but 2.0 on ubuntu1604 worked.  The atlasproxy project
# itself tests on rhel70.
#
# This script expects the following environment variables:
#
# DRIVERS_TOOLS (required) - absolute path to the checked out
# driver-evergreen-tools repository
#
# MONGODB_VERSION - version of MongoDB to download and use. For Atlas
# Proxy, must be "3.4" or "latest".  Defaults to "3.4".

set -o xtrace   # Write all commands first to stderr
set -o errexit  # Exit the script with error if any of the commands fail

MONGODB_VERSION=${MONGODB_VERSION:-"3.4"}

ORIG_DIR="$(pwd)"

#--------------------------------------------------------------------------#
# Downlaod MongoDB Binary
#--------------------------------------------------------------------------#

DL_START=$(date +%s)
DIR=$(dirname $0)

# Load download helper functions
. $DIR/download-mongodb.sh

# set $DISTRO
get_distro

# set $MONGODB_DOWNLOAD_URL and $EXTRACT
get_mongodb_download_url_for "$DISTRO" "$MONGODB_VERSION"

# extracts to $DRIVERS_TOOLS/mongodb
rm -rf "$DRIVERS_TOOLS/mongodb"
download_and_extract "$MONGODB_DOWNLOAD_URL" "$EXTRACT"
DL_END=$(date +%s)

#--------------------------------------------------------------------------#
# Clone Atlas Proxy repo and launch it
#--------------------------------------------------------------------------#

AP_START=$(date +%s)

cd "$ORIG_DIR"
rm -rf atlasproxy
git clone git@github.com:10gen/atlasproxy.git
cd atlasproxy

# This section copied from atlasproxy's .evergreen.yml: <<<
export PATH="/opt/golang/go1.11/bin:$PATH"
export GOROOT="/opt/golang/go1.11"
export GOPATH=`pwd`/.gopath
go version
./gpm
export MONGO_DIR="$DRIVERS_TOOLS/mongodb/bin"
cd main
./start_test_proxies_and_mtms.sh
# >>> end of copy

AP_END=$(date +%s)

# Write results file
DL_ELAPSED=$(expr $DL_END - $DL_START)
AP_ELAPSED=$(expr $AP_END - $AP_START)
cat <<EOT >> $DRIVERS_TOOLS/results.json
{"results": [
  {
    "status": "PASS",
    "test_file": "AtlasProxy Start",
    "start": $AP_START,
    "end": $AP_END,
    "elapsed": $AP_ELAPSED
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

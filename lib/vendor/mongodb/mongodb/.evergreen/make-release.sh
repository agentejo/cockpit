#!/bin/sh
set -o xtrace   # Write all commands first to stderr
set -o errexit  # Exit the script with error if any of the commands fail


echo "Creating Release Archive Bundle RPM"

echo "The release archive will have the version automatically derived from the nearest git tag for patchbuilds, otherwise 'latest' is used"

echo "The release file should be called $PROJECT.tar.gz"


cd .. && tar czf $PROJECT.tar.gz src

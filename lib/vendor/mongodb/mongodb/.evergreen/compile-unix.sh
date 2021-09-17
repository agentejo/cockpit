#!/bin/sh
set -o xtrace   # Write all commands first to stderr
set -o errexit  # Exit the script with error if any of the commands fail

# Supported/used environment variables:
#       MARCH                   Machine Architecture. Defaults to lowercase uname -m
#       RELEASE                 Use the fully qualified release archive

RELEASE=${RELEASE:-no}


# Automatically retrieve the machine architecture, lowercase, unless provided
# as an environment variable (e.g. to force 32bit)
[ -z "$MARCH" ] && MARCH=$(uname -m | tr '[:upper:]' '[:lower:]')

# Get the kernel name, lowercased
OS=$(uname -s | tr '[:upper:]' '[:lower:]')
echo "OS: $OS"

# --strip-components is an GNU tar extension. Check if the platform
# (e.g. Solaris) has GNU tar installed as `gtar`, otherwise we assume to be on
# platform that supports it
# command -v returns success error code if found and prints the path to it
if command -v gtar 2>/dev/null; then
   TAR=gtar
else
   TAR=tar
fi

# Any architecture specific configuration here
case "$MARCH" in
   i386)
      CFLAGS="$CFLAGS -m32 -march=i386"
   ;;
   x86_64)
      CFLAGS="$CFLAGS -m64 -march=x86-64"
   ;;
   ppc64le)
      CFLAGS="$CFLAGS -mcpu=power8 -mtune=power8 -mcmodel=medium"
   ;;
esac


# Operating system specific tweaks
case "$OS" in
   darwin)
   ;;

   linux)
      # Make linux builds a tad faster by parallelise the build
      cpus=$(grep -c '^processor' /proc/cpuinfo)
      MAKEFLAGS="-j${cpus}"
   ;;

   sunos)
      # Most normal build tools on the Solaris servers lives here
      PATH="/opt/mongodbtoolchain/bin:$PATH"
   ;;
esac

echo "MARCH: $MARCH"
echo "RELEASE: $RELEASE"
echo "OS: $OS"


#./configure
make

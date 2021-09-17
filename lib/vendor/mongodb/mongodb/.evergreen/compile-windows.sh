#!/bin/sh
set -o igncr    # Ignore CR in this script
set -o xtrace   # Write all commands first to stderr
set -o errexit  # Exit the script with error if any of the commands fail

# Supported/used environment variables:
#       CC            Which compiler to use


case "$CC" in
   # 64bit specific configuration
   *Win64)
   ;;
   # 32bit specific configuration
   *)
   ;;
esac

# Resolve the compiler name to correct MSBuild location
case "$CC" in
   "Visual Studio 10 2010")
      BUILD="/cygdrive/c/Windows/Microsoft.NET/Framework/v4.0.30319/MSBuild.exe"
   ;;
   "Visual Studio 10 2010 Win64")
      BUILD="/cygdrive/c/Windows/Microsoft.NET/Framework64/v4.0.30319/MSBuild.exe"
   ;;
   "Visual Studio 12 2013")
      BUILD="/cygdrive/c/Program Files (x86)/MSBuild/12.0/Bin/MSBuild.exe"
   ;;
   "Visual Studio 12 2013 Win64")
      BUILD="/cygdrive/c/Program Files (x86)/MSBuild/12.0/Bin/MSBuild.exe"
   ;;
   "Visual Studio 14 2015")
      BUILD="/cygdrive/c/Program Files (x86)/MSBuild/14.0/Bin/MSBuild.exe"
   ;;
   "Visual Studio 14 2015 Win64")
      BUILD="/cygdrive/c/Program Files (x86)/MSBuild/14.0/Bin/MSBuild.exe"
   ;;
esac

export PATH=$PATH:`pwd`/tests:`pwd`/Debug:`pwd`/src/libbson/Debug
CMAKE="/cygdrive/c/cmake/bin/cmake"
INSTALL_DIR="C:/install-dir"


"$CMAKE" -G "$CC" "-DCMAKE_INSTALL_PREFIX=${INSTALL_DIR}" "-DBSON_ROOT_DIR=${INSTALL_DIR}" $CONFIGURE_FLAGS
"$BUILD" /m ALL_BUILD.vcxproj
"$BUILD" /m INSTALL.vcxproj


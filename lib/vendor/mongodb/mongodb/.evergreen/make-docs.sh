#!/bin/sh
set -o xtrace   # Write all commands first to stderr
set -o errexit  # Exit the script with error if any of the commands fail


mkdir -p doc/html || true


cat <<EOT > doc/html/index.html > doc/html/intro.html
<html>
<body>
<ul>
<li><a href="index.html">Index</a></li>
<li><a href="intro.html">Intro</a></li>
</ul>
EOT

cat <<EOT >> doc/html/index.html
This is an example of a doc page automatically uploaded to evergreen so you can see your docs rendered.
The evergreen URL differs for patch builds and normal builds
EOT
cat <<EOT >> doc/html/intro.html
This page is never actually uploaded by evergreen, only the index page was uploaded.
Thats why there is a link to the index page ("Rendered docs") while this is not an actual artifact.
This page was uploaded seperately with the s3cmd tools
EOT

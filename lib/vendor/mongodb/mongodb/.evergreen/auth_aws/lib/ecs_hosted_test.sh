#!/bin/bash
# A shell script to run in an ECS hosted task

# The environment variable is always set during interactive logins
# But for non-interactive logs, ~/.bashrc does not appear to be read on Ubuntu but it works on Fedora
[[ -z "${AWS_CONTAINER_CREDENTIALS_RELATIVE_URI}" ]] && export $(strings /proc/1/environ | grep AWS_CONTAINER_CREDENTIALS_RELATIVE_URI)

env

mkdir -p /data/db || true

/root/mongo  --verbose --nodb ecs_hosted_test.js

RET_CODE=$?
echo RETURN CODE: $RET_CODE
exit $RET_CODE

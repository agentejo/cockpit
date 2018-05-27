#!/bin/bash
# Copyright 2012-2014 MongoDB, Inc.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
# http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

function eval_params {
    local params=$(sed -e 's|["]|\\\"|g' $1)
    echo $(eval echo \"$params\")
} 

function r {
    echo $1| cut -d'/' -f 2
}

function a {
    echo $(cd $(dirname $1); pwd)/$(basename $1)
}

function id {
    local id_line=$(grep id $1 | head -n 1)
    echo $(expr "$id_line" : '.*: *"\(.*\)" *,*')
}

function get {
    echo "GET $1 $(curl --header 'Accept: application/json' --include --silent --request GET $1)"
}

function post {
    echo "POST $1 $(curl --header 'Accept: application/json' --include --silent --request POST --data "$2" $1)"
}

function delete {
    echo "DELETE $1 $(curl --header 'Accept: application/json' --include --silent --request DELETE $1)"
}

function code {
   expr "$1" : '.*HTTP/1.[01] \([0-9]*\)'
}

function usage {
    echo "usage: $0 configurations/cluster/file.json action"
    echo "cluster: servers|replica_sets|sharded_clusters"
    echo "action: start|status|stop"
    exit 1
}

SSL_FILES=$(a ./ssl-files)
BASE_URL=${MONGO_ORCHESTRATION:-'http://localhost:8889'}

if [ $# -ne 2 ]; then usage; fi
if [ ! -f "$1" ]; then echo "configuration file '$1' not found"; exit 1; fi

ID=$(id $1)
if [ ! "$ID" ]; then echo "id field not found in configuration file '$1'"; exit 1; fi
R=$(r $1)

GET=$(get $BASE_URL/$R/$ID)
HTTP_CODE=$(code "$GET")
EXIT_CODE=0

case $2 in
start)
    if [ "$HTTP_CODE" != "200" ]
    then
        WORKSPACE=~/tmp/orchestrations
        rm -fr $WORKSPACE
        mkdir $WORKSPACE
        LOGPATH=$WORKSPACE
        DBPATH=$WORKSPACE
        POST_DATA=$(eval_params $1)
        echo "DBPATH=$DBPATH"
        echo "LOGPATH=$LOGPATH"
        echo "POST_DATA='$POST_DATA'"
        echo
        POST=$(post $BASE_URL/$R "$POST_DATA")
        echo "$POST"
        HTTP_CODE=$(code "$POST")
        if [ "$HTTP_CODE" != 200 ]; then EXIT_CODE=1; fi
    else
        echo "$GET"
    fi
    ;;
stop)
    if [ "$HTTP_CODE" == "200" ]
    then
        DELETE=$(delete $BASE_URL/$R/$ID)
        echo "$DELETE"
        HTTP_CODE=$(code "$DELETE")
        if [ "$HTTP_CODE" != 204 ]; then EXIT_CODE=1; fi
    else
        echo "$GET"
    fi
    ;;
status)
    if [ "$HTTP_CODE" == "200" ]
    then
        echo "$GET"
    else
        echo "$GET"
        EXIT_CODE=1
    fi
    ;;
*)
    usage
    ;;
esac
exit $EXIT_CODE

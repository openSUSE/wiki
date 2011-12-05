#!/bin/sh

LANGUAGE=$1
COMMAND=$2
shift 2

sed -i "1 a \$_SERVER['SERVER_NAME'] = \'$LANGUAGE.opensuse.org\';" $COMMAND

echo "--------------------------------------
Running $COMMAND $* for $LANGUAGE.opensuse.org
--------------------------------------";

php $COMMAND $*
sed -i '/SERVER_NAME/d' $COMMAND

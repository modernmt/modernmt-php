#!/bin/bash

VERSION="$1"

if [[ ! "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
	echo "Invalid version number"
	exit 1
fi

# shellcheck disable=SC2016
match='        if \(\$platformVersion == null\) \$platformVersion ='
ver="        if \(\$platformVersion == null\) \$platformVersion = '${VERSION}';"
sed -i -E "/$match/s/.*/$ver/" src/ModernMT.php

#!/bin/bash

VERSION="$1"

if [[ ! "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
	echo "Invalid version number"
	exit 1
fi

# header
match="    public function __construct\("
ver="    public function __construct\(\$license, \$platform = 'modernmt-php', \$platformVersion = '${VERSION}'\) {"
sed -i -E "/$match/s/.*/$ver/" src/ModernMT.php

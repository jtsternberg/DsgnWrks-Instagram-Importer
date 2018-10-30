#!/usr/bin/env bash
# To install temp. test suite
# bash tests/bin/install-wp-tests.sh wordpress_test root ''

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
DB_PORT=${DB_PORT-${6-''}}

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wordpress/}

if [[ "$DB_NAME" == "" || "$DB_USER" == "" ]] ; then
	echo ""
	echo "Usage:      bash $0 <db-name> <db-user> [db-pass=''] [db-host='localhost'] [wp-version='latest']"
	echo "Suggestion: bash $0 wordpress_test root ''"
	echo ""
	exit 1
fi

download() {
	if [ `which curl` ]; then
		curl -s "$1" > "$2";
	elif [ `which wget` ]; then
		wget -nv -O "$2" "$1"
	fi
}

if [[ $WP_VERSION =~ [0-9]+\.[0-9]+(\.[0-9]+)? ]]; then
	WP_TESTS_TAG="tags/$WP_VERSION"
else
	# http serves a single offer, whereas https serves multiple. we only want one
	download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
	grep '[0-9]+\.[0-9]+(\.[0-9]+)?' /tmp/wp-latest.json
	LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')
	if [[ -z "$LATEST_VERSION" ]]; then
		echo "Latest WordPress version could not be found"
		exit 1
	fi
	WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

set -ex

install_wp() {

	# delete existing
	if [ -d "$WP_CORE_DIR" ]; then
		rm -rf $WP_CORE_DIR
	fi

	mkdir -p "$WP_CORE_DIR"

	if [ "$WP_VERSION" == 'latest' ]; then
		local ARCHIVE_NAME='latest'
	else
		local ARCHIVE_NAME="wordpress-$WP_VERSION"
	fi

	download https://wordpress.org/"${ARCHIVE_NAME}".tar.gz  /tmp/wordpress.tar.gz
	tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C "$WP_CORE_DIR"

	download https://raw.github.com/markoheijnen/wp-mysqli/master/db.php "$WP_CORE_DIR"wp-content/db.php

	if [ ! -d "${WP_CORE_DIR}tests/data/themedir1/dummy-theme/" ]; then
		mkdir -p "${WP_CORE_DIR}tests/data/themedir1/dummy-theme/"
	fi
}

install_test_suite() {
	CONFIG="${WP_TESTS_DIR}/wp-tests-config.php";
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' && $(which sed) == '/usr/bin/sed' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	# delete existing
	if [ -d "$WP_TESTS_DIR" ]; then
		rm -rf $WP_TESTS_DIR
	fi


	if [ -d "$WP_TESTS_DIR/includes" ]; then

		rm -rf "$WP_TESTS_DIR/includes"
	fi

	# set up testing suite
	mkdir -p $WP_TESTS_DIR
	svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ $WP_TESTS_DIR/includes

	cd $WP_TESTS_DIR

	download https://develop.svn.wordpress.org/"${WP_TESTS_TAG}"/wp-tests-config-sample.php $CONFIG
	sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR':" $CONFIG
	sed $ioption "s:define( 'WP_DEBUG', true );:define( 'WP_DEBUG', true ); define( 'WP_DEBUG_DISPLAY', false ); define( 'WP_DEBUG_LOG', true );:" "$WP_TESTS_DIR"/wp-tests-config.php
	sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" $CONFIG
	sed $ioption "s/yourusernamehere/$DB_USER/" $CONFIG
	sed $ioption "s/yourpasswordhere/$DB_PASS/" $CONFIG
	sed $ioption "s|localhost|${DB_HOST}|" $CONFIG
}

install_db() {
	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]-${DB_PORT}};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# drop database
	mysql --user="$DB_USER" --password="$DB_PASS"$EXTRA -e "DROP DATABASE IF EXISTS $DB_NAME"

	# create database
	mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA

	# Increase the max allowed packet size.
	mysql --user="$DB_USER" --password="$DB_PASS"$EXTRA -e "set global net_buffer_length=1000000;set global max_allowed_packet=1000000000;"
}

install_wp
install_test_suite
install_db

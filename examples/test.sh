#!/bin/sh

PHP_BIN=php
PEAR_DIR=/usr/local/share/pear
ZEND_DIR="${HOME}/Sites/lib/ZendFramework"
TEST_DIR="${PEAR_DIR}/docs/Services_Estraier/examples"

case "$1" in
	51)
		TEST_DIR=${PEAR_DIR}/docs/Services_Estraier/examples
		PHP_BIN=/usr/local/php-5.1
		;;
	52)
		TEST_DIR=${PEAR_DIR}/docs/Services_Estraier/examples
		PHP_BIN=/usr/local/php-5.2
		;;
esac

INCLUDE_PATH="${TEST_DIR}:${PEAR_DIR}:${ZEND_DIR}"

$PHP_BIN -d include_path="$INCLUDE_PATH" "${TEST_DIR}/search.php"
$PHP_BIN -d include_path="$INCLUDE_PATH" "${TEST_DIR}/register.php"
$PHP_BIN -d include_path="$INCLUDE_PATH" "${TEST_DIR}/search.php"
$PHP_BIN -d include_path="$INCLUDE_PATH" "${TEST_DIR}/purge.php"
$PHP_BIN -d include_path="$INCLUDE_PATH" "${TEST_DIR}/search.php"

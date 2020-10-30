#!/usr/bin/env bash

set -e

source $(dirname ${BASH_SOURCE[0]})/set-env-vars.sh

VERBOSITY=
RESET=false
COVERAGE=

while getopts ":c:vr" opt
do
    case $opt in
        c)
            COVERAGE="--coverage-clover=${OPTARG}"
        ;;
        r)
            RESET=true
        ;;
        v)
            VERBOSITY=-v
        ;;
    esac
done
shift $((OPTIND-1))

if [ "${RESET}" = true ]; then
    echo "Resetting the database..."
    $(dirname ${BASH_SOURCE[0]})/create-db.sh
    echo "Purging eZ caches..."
    # Some manipulations make the SF console fail to run - that's why we prefer to clear the cache via file purge
    #$(dirname ${BASH_SOURCE[0]})/sfconsole.sh ${VERBOSITY} cache:clear
    $(dirname ${BASH_SOURCE[0]})/cleanup.sh ez-cache
    echo "Running the tests..."
fi

# Note: make sure we run the version of phpunit we installed, not the system one. See: https://github.com/sebastianbergmann/phpunit/issues/2014

# @todo pass to phpunit any further cli options, or at least allow to specify a single test using --filter

$(dirname $(dirname $(dirname ${BASH_SOURCE[0]})))/vendor/phpunit/phpunit/phpunit --stderr --colors ${VERBOSITY} ${COVERAGE} Tests/phpunit "$@"

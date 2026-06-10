#!/usr/bin/env bash
set -euo pipefail

CONTAINER="$1"

docker cp ./tests/unit/Data/SqlMap/scripts/ibm/database.sql "$CONTAINER":/tmp/initdb.sql
docker exec "$CONTAINER" \
  su - db2inst1 -c "db2 connect to pradount && db2 -td\; -f /tmp/initdb.sql"

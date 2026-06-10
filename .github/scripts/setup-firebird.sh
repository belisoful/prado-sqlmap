#!/usr/bin/env bash
set -euo pipefail

CONTAINER="$1"

docker cp ./tests/unit/Data/SqlMap/scripts/firebird/database.sql "$CONTAINER":/tmp/initdb_firebird.sql
docker exec "$CONTAINER" bash -c "
  ISQL=\$(which isql-fb 2>/dev/null || which isql 2>/dev/null)
  if [ -z \"\$ISQL\" ]; then echo 'isql binary not found'; exit 1; fi
  OUTPUT=\$(\"\$ISQL\" localhost:/var/lib/firebird/data/prado_unitest.fdb \
    -user SYSDBA -password masterkey \
    -i /tmp/initdb_firebird.sql 2>&1)
  printf '%s\n' \"\$OUTPUT\"
  # DROP statements on a fresh database produce 'object unknown' (-204) — allow those.
  # Any other 'Statement failed' indicates a real schema error.
  if echo \"\$OUTPUT\" | grep 'Statement failed' | grep -qv 'object unknown\|Table unknown\|Sequence unknown\|does not exist'; then
    exit 1
  fi
"

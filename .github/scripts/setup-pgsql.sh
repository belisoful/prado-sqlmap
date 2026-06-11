#!/usr/bin/env bash
set -euo pipefail

# Create the login role and a database owned by it.
PGPASSWORD=postgres psql -h 127.0.0.1 -U postgres \
  -c "CREATE ROLE prado_unitest WITH LOGIN PASSWORD 'prado_unitest';"
PGPASSWORD=postgres createdb -h 127.0.0.1 -U postgres -O prado_unitest prado_unitest

# PostgreSQL 15+ no longer grants CREATE on the public schema to non-owners.
# Hand the public schema to prado_unitest so the test suite's initSchema (which
# re-runs database.sql as prado_unitest, dropping and recreating tables) owns
# every object it touches.
PGPASSWORD=postgres psql -h 127.0.0.1 -U postgres -d prado_unitest \
  -c "ALTER SCHEMA public OWNER TO prado_unitest;"

# Load the schema as prado_unitest so the objects are owned by the test user.
PGPASSWORD=prado_unitest psql -h 127.0.0.1 -U prado_unitest -d prado_unitest \
  -a -w -f ./tests/unit/Data/SqlMap/scripts/pgsql/database.sql

#!/usr/bin/env bash
set -euo pipefail

export PGPASSWORD=postgres

psql -h 127.0.0.1 -U postgres -c "CREATE ROLE prado_unitest WITH LOGIN PASSWORD 'prado_unitest';"
createdb -h 127.0.0.1 -U postgres prado_unitest
psql -h 127.0.0.1 -U postgres -a -w prado_unitest -f ./tests/unit/Data/SqlMap/scripts/pgsql/database.sql
psql -h 127.0.0.1 -U postgres prado_unitest -c "GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO prado_unitest;"
psql -h 127.0.0.1 -U postgres prado_unitest -c "GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO prado_unitest;"

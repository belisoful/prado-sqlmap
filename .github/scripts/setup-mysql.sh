#!/usr/bin/env bash
set -euo pipefail

sudo systemctl start mysql.service
mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS prado_unitest DEFAULT CHARACTER SET latin1;"
mysql -uroot -proot prado_unitest < ./tests/unit/Data/SqlMap/scripts/mysql/DataBase.sql

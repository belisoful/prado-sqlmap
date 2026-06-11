#!/usr/bin/env bash
set -euo pipefail

sudo systemctl start mysql.service
mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS prado_unitest DEFAULT CHARACTER SET latin1;"
mysql -uroot -proot -e "CREATE USER IF NOT EXISTS 'prado_unitest'@'localhost' IDENTIFIED BY 'prado_unitest';"
mysql -uroot -proot -e "GRANT ALL PRIVILEGES ON prado_unitest.* TO 'prado_unitest'@'localhost';"
mysql -uroot -proot -e "FLUSH PRIVILEGES;"
mysql -uroot -proot prado_unitest < ./tests/unit/Data/SqlMap/scripts/mysql/DataBase.sql

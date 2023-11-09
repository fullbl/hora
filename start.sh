#!/bin/sh
cd gest-be
docker compose up -d
cd ../gest-fe
docker compose up -d
cd ..
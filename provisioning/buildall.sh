#!/usr/bin/env bash

docker build -f base/Dockerfile -t ps2alerts-api:base base
docker build -f dev/Dockerfile -t ps2alerts-api:dev dev
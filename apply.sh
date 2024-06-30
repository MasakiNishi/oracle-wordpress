#!/bin/bash

count=0

while true; do
  count=$((count+1))
  echo "Try $count"
  terraform apply -auto-approve
  if [ $? -eq 0 ]; then
    echo "Resource Created!"
    exit 0
  fi
  sleep 300
done

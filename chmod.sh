#!/bin/bash
# chmod.sh - Set correct permissions for TakeOff Hotel IPTV System
# Run this after git pull or deployment

echo "Setting permissions for uploads folder..."
chmod -R 777 uploads/

echo "Setting permissions for storage (if exists)..."
chmod -R 777 storage/ 2>/dev/null || true

echo "Setting permissions for cache (if exists)..."
chmod -R 777 cache/ 2>/dev/null || true

echo "Done! Permissions set."
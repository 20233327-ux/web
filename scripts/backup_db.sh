#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -lt 5 ]; then
  echo "Usage: $0 <host> <port> <user> <database> <output_dir>"
  exit 1
fi

HOST="$1"
PORT="$2"
USER="$3"
DB="$4"
OUTDIR="$5"

mkdir -p "$OUTDIR"
TS="$(date +%Y%m%d_%H%M%S)"
OUTFILE="$OUTDIR/${DB}_${TS}.sql"

echo "Nhap mat khau MySQL khi duoc hoi..."
mysqldump -h "$HOST" -P "$PORT" -u "$USER" --single-transaction --routines --triggers --events "$DB" > "$OUTFILE"
echo "Backup thanh cong: $OUTFILE"

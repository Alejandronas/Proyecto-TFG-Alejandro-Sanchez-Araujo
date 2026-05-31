#!/bin/bash

SEP="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "$SEP"
echo " Uso de memoria (free -h)"
echo "$SEP"
free -h

echo ""
echo "$SEP"
echo " Detalle /proc/meminfo"
echo "$SEP"
grep -E "MemTotal|MemFree|MemAvailable|SwapTotal|SwapFree|Cached|Buffers" /proc/meminfo

echo ""
echo "$SEP"
echo " Top 10 procesos por consumo de RAM"
echo "$SEP"
ps aux --sort=-%mem | head -11

echo ""
echo "$SEP"
echo " Uso de swap por proceso (Top 10)"
echo "$SEP"
for f in /proc/*/status; do
    awk '/VmSwap|Name/{printf $2" "}END{print ""}' "$f" 2>/dev/null
done | sort -k2 -n -r | head -10

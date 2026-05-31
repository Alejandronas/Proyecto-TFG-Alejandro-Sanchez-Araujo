#!/bin/bash

SEP="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "$SEP"
echo " Tamaño de entradas en /var/log (ordenado desc)"
echo "$SEP"
du -sh /var/log/* 2>/dev/null | sort -hr | head -20 \
    || echo "  Sin acceso a /var/log"

echo ""
echo "$SEP"
echo " Archivos .log más pesados"
echo "$SEP"
find /var/log -name "*.log" -type f 2>/dev/null \
    | xargs du -sh 2>/dev/null \
    | sort -hr | head -15 \
    || echo "  Sin archivos .log"

echo ""
echo "$SEP"
echo " Archivos .log modificados en las últimas 24 horas"
echo "$SEP"
find /var/log -name "*.log" -newer /proc/1 -type f 2>/dev/null \
    | head -20 \
    || find /var/log -name "*.log" -mtime -1 -type f 2>/dev/null | head -20 \
    || echo "  (Sin modificaciones recientes o sin acceso)"

echo ""
echo "$SEP"
echo " Espacio total de /var/log"
echo "$SEP"
du -sh /var/log 2>/dev/null || echo "  Sin acceso"

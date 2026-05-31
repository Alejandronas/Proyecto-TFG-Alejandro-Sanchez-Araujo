#!/bin/bash

SEP="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "$SEP"
echo " Uptime y carga media"
echo "$SEP"
uptime

echo ""
echo "$SEP"
echo " Número de CPUs lógicas"
echo "$SEP"
nproc

echo ""
echo "$SEP"
echo " Información del procesador"
echo "$SEP"
lscpu | grep -E "Model name|CPU\(s\)|CPU MHz|Thread|Core|Socket"

echo ""
echo "$SEP"
echo " Top 10 procesos por consumo de CPU"
echo "$SEP"
ps aux --sort=-%cpu | head -11

echo ""
echo "$SEP"
echo " vmstat – estadísticas de CPU (3 muestras)"
echo "$SEP"
vmstat 1 3

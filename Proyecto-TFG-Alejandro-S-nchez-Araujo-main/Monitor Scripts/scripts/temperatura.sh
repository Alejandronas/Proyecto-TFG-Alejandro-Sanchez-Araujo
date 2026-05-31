#!/bin/bash

SEP="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "$SEP"
echo " Temperatura por zonas térmicas (/sys/class/thermal)"
echo "$SEP"
found=0
for f in /sys/class/thermal/thermal_zone*/temp; do
    [ -f "$f" ] || continue
    val=$(cat "$f")
    grados=$(echo "$val / 1000" | bc 2>/dev/null || echo "?")
    zone=$(echo "$f" | grep -oP 'thermal_zone\d+')
    echo "  $zone : ${val} mC  →  ${grados} °C"
    found=1
done
[ "$found" -eq 0 ] && echo "  (No hay zonas térmicas disponibles)"

echo ""
echo "$SEP"
echo " sensors (lm-sensors)"
echo "$SEP"
if command -v sensors &>/dev/null; then
    sensors
else
    echo "  lm-sensors no instalado. Instala con: sudo apt install lm-sensors"
fi

echo ""
echo "$SEP"
echo " acpi – temperatura"
echo "$SEP"
if command -v acpi &>/dev/null; then
    acpi -t
else
    echo "  acpi no disponible. Instala con: sudo apt install acpi"
fi

echo ""
echo "$SEP"
echo " Frecuencia actual de CPUs"
echo "$SEP"
if ls /sys/devices/system/cpu/cpu*/cpufreq/scaling_cur_freq &>/dev/null; then
    for f in /sys/devices/system/cpu/cpu*/cpufreq/scaling_cur_freq; do
        cpu=$(echo "$f" | grep -oP 'cpu\d+')
        freq=$(cat "$f")
        mhz=$(echo "$freq / 1000" | bc)
        echo "  $cpu : ${freq} kHz  →  ${mhz} MHz"
    done
else
    echo "  (cpufreq no disponible en este kernel/VM)"
fi

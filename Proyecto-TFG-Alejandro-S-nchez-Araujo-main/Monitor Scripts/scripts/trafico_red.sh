#!/bin/bash

SEP="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "$SEP"
echo " Interfaces de red activas"
echo "$SEP"
ip link show

echo ""
echo "$SEP"
echo " Estadísticas RX/TX por interfaz"
echo "$SEP"
cat /proc/net/dev

echo ""
echo "$SEP"
echo " Interfaces virtuales (Bridge / NAT: virbr, vboxnet, vmnet, br-)"
echo "$SEP"
ip link show | grep -E "virbr|vboxnet|vmnet|br-|docker" \
    || echo "  (Sin interfaces virtuales detectadas)"

echo ""
echo "$SEP"
echo " Tabla de rutas activas"
echo "$SEP"
ip route show

echo ""
echo "$SEP"
echo " Conexiones TCP activas (top 20)"
echo "$SEP"
ss -tnp 2>/dev/null | head -21 \
    || netstat -tnp 2>/dev/null | head -21 \
    || echo "  (ss/netstat no disponible)"

#!/bin/bash

SEP="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "$SEP"
echo " Todos los puertos TCP/UDP en escucha"
echo "$SEP"
ss -tlnup 2>/dev/null \
    || netstat -tlnup 2>/dev/null \
    || echo "  (ss/netstat no disponible)"

echo ""
echo "$SEP"
echo " Puertos Vagrant frecuentes (2222, 8080, 8443, 3306, 5432)"
echo "$SEP"
for puerto in 2222 8080 8443 3306 5432 80 443 22; do
    resultado=$(ss -tlnp 2>/dev/null | grep ":${puerto} " || echo "")
    if [ -n "$resultado" ]; then
        echo "  [ABIERTO]  :$puerto  →  $resultado"
    else
        echo "  [cerrado]  :$puerto"
    fi
done

echo ""
echo "$SEP"
echo " Reglas de reenvío activas (iptables DNAT)"
echo "$SEP"
iptables -t nat -L PREROUTING -n --line-numbers 2>/dev/null \
    || echo "  (iptables no disponible o sin permisos)"

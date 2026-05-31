#!/bin/bash

PAQUETES="${1:-4}"
shift
HOSTS="$@"
SEP="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ -z "$HOSTS" ]; then
    echo "ERROR: Debes indicar al menos un host"
    exit 1
fi

echo "$SEP"
echo " Ping a $# host(s)  |  $PAQUETES paquetes por host"
echo "$SEP"

for host in $HOSTS; do
    echo ""
    echo " $host"
    result=$(ping -c "$PAQUETES" -W 2 "$host" 2>&1)
    echo "$result" | grep -E "PING|rtt|min/avg|packet loss|packets transmitted"
    if echo "$result" | grep -q "0% packet loss"; then
        echo "  Estado: ALCANZABLE "
    else
        echo "  Estado: PÉRDIDA DE PAQUETES "
    fi
done

echo ""
echo "$SEP"
echo " Resumen completado"
echo "$SEP"

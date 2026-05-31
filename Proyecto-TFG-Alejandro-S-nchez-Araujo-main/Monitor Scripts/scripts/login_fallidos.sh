#!/bin/bash

LINEAS="${1:-50}"
SEP="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "$SEP"
echo " Últimos $LINEAS intentos fallidos de login"
echo "$SEP"


if [ -f /var/log/auth.log ]; then
    echo ">> Fuente: /var/log/auth.log"
    grep -iE "failed|failure|invalid|refused" /var/log/auth.log 2>/dev/null \
        | tail -"$LINEAS"

elif [ -f /var/log/secure ]; then
    echo ">> Fuente: /var/log/secure"
    grep -iE "failed|failure|invalid|refused" /var/log/secure 2>/dev/null \
        | tail -"$LINEAS"
else
    echo "  (Sin /var/log/auth.log ni /var/log/secure – intentando journalctl)"
fi

echo ""
echo "$SEP"
echo " Intentos fallidos vía journalctl (últimas 24h)"
echo "$SEP"
if command -v journalctl &>/dev/null; then
    journalctl --since "24 hours ago" 2>/dev/null \
        | grep -iE "failed password|invalid user|authentication failure" \
        | tail -"$LINEAS"
else
    echo "  journalctl no disponible"
fi

echo ""
echo "$SEP"
echo " IPs con más intentos fallidos (Top 10)"
echo "$SEP"
(
    grep -iE "failed|invalid" /var/log/auth.log 2>/dev/null
    grep -iE "failed|invalid" /var/log/secure 2>/dev/null
    journalctl --since "24 hours ago" 2>/dev/null | grep -iE "failed|invalid"
) | grep -oE "([0-9]{1,3}\.){3}[0-9]{1,3}" \
    | sort | uniq -c | sort -rn | head -10 \
    || echo "  (No se pudieron extraer IPs)"

echo ""
echo "$SEP"
echo " Estadísticas rápidas (lastb – últimos logins fallidos)"
echo "$SEP"
if command -v lastb &>/dev/null; then
    lastb 2>/dev/null | head -20 || echo "  (lastb requiere permisos de root)"
else
    echo "  lastb no disponible"
fi

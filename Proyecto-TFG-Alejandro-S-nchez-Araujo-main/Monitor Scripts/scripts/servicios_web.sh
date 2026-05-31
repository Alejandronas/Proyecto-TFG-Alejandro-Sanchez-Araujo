#!/bin/bash

SEP="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

SERVICIOS="apache2 nginx httpd mysql mariadb postgresql mongod redis php8.1-fpm php7.4-fpm"

echo "$SEP"
echo " Estado de servicios (systemctl is-active)"
echo "$SEP"
for svc in $SERVICIOS; do
    estado=$(systemctl is-active "$svc" 2>/dev/null)
    if [ "$estado" = "active" ]; then
        icono="[OK]"
    elif [ "$estado" = "inactive" ]; then
        icono="[--]"
    else
        icono="[??]"
    fi
    printf "  %s  %-22s %s\n" "$icono" "$svc" "$estado"
done

echo ""
echo "$SEP"
echo " Puertos web/db escuchando (80, 443, 3306, 5432, 27017, 6379, 8080, 8443)"
echo "$SEP"
ss -tlnp 2>/dev/null | grep -E ":80 |:443 |:3306|:5432|:27017|:6379|:8080|:8443" \
    || netstat -tlnp 2>/dev/null | grep -E ":80 |:443 |:3306|:5432" \
    || echo "  (ss/netstat no disponible o sin servicios activos)"

echo ""
echo "$SEP"
echo " Procesos web/db activos"
echo "$SEP"
ps aux | grep -E "apache2|nginx|mysql|postgres|mongod|redis" | grep -v grep \
    || echo "  Ningún proceso web/db encontrado"

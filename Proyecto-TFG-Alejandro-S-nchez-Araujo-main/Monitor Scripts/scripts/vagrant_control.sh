#!/bin/bash

DIRECTORIO="$1"
COMANDO="$2"
VM="$3"
SEP="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ -z "$DIRECTORIO" ] || [ -z "$COMANDO" ]; then
    echo "ERROR: Uso: vagrant_control.sh <directorio> <comando> [vm]"
    exit 1
fi

if [ ! -d "$DIRECTORIO" ]; then
    echo "ERROR: Directorio no existe: $DIRECTORIO"
    exit 1
fi

echo "$SEP"
echo " Ejecutando: vagrant $COMANDO $VM"
echo " Directorio: $DIRECTORIO"
echo "$SEP"
echo ""

cd "$DIRECTORIO" || exit 1

if command -v vagrant &>/dev/null; then
    vagrant $COMANDO $VM
else
    echo "  vagrant no encontrado en el PATH"
fi

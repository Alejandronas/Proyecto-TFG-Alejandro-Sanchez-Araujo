#!/bin/bash

DIRECTORIO="$1"
VM="$2"
SEP="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ -z "$DIRECTORIO" ]; then
    echo "ERROR: Debes indicar el directorio del Vagrantfile"
    exit 1
fi

if [ ! -d "$DIRECTORIO" ]; then
    echo "ERROR: Directorio no existe: $DIRECTORIO"
    exit 1
fi

echo "$SEP"
echo " vagrant ssh-config — Directorio: $DIRECTORIO  VM: ${VM:-default}"
echo "$SEP"
cd "$DIRECTORIO" || exit 1

if command -v vagrant &>/dev/null; then
    vagrant ssh-config $VM 2>&1
else
    echo "  vagrant no encontrado en el PATH"
fi

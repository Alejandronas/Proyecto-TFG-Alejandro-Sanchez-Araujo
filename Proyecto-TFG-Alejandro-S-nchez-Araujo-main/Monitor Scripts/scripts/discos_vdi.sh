#!/bin/bash

DIRECTORIO="${1:-$HOME}"
SEP="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "$SEP"
echo " Uso de particiones (df -h)"
echo "$SEP"
df -h

echo ""
echo "$SEP"
echo " Uso de particiones (df -h --type=ext4 --type=xfs --type=btrfs)"
echo "$SEP"
df -h -t ext4 -t xfs -t btrfs -t vfat 2>/dev/null || df -h

echo ""
echo "$SEP"
echo " Dispositivos de bloque (lsblk)"
echo "$SEP"
lsblk 2>/dev/null || echo "  (lsblk no disponible)"

echo ""
echo "$SEP"
echo " Buscando archivos .vdi y .vmdk en: $DIRECTORIO"
echo "$SEP"
find "$DIRECTORIO" \( -name "*.vdi" -o -name "*.vmdk" \) 2>/dev/null \
    | xargs -I{} du -sh {} 2>/dev/null \
    | sort -h \
    || echo "  No se encontraron archivos .vdi o .vmdk"

echo ""
echo "$SEP"
echo " Espacio total ocupado por discos virtuales"
echo "$SEP"
total=$(find "$DIRECTORIO" \( -name "*.vdi" -o -name "*.vmdk" \) 2>/dev/null \
    | xargs du -sc 2>/dev/null | tail -1 | awk '{print $1}')
if [ -n "$total" ] && [ "$total" != "0" ]; then
    echo "  Total: $(echo "$total / 1024" | bc) MB aprox."
else
    echo "  (Ningún disco virtual encontrado en $DIRECTORIO)"
fi

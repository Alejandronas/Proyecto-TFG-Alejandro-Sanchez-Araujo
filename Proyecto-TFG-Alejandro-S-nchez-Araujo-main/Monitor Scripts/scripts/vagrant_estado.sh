#!/bin/bash

SEP="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

echo "$SEP"
echo " vagrant global-status"
echo "$SEP"
if command -v vagrant &>/dev/null; then
    vagrant global-status 2>&1
else
    echo "  vagrant no encontrado en el PATH"
    echo "  Instala con: sudo apt install vagrant  o  descarga de vagrantup.com"
fi

echo ""
echo "$SEP"
echo " Procesos VirtualBox activos"
echo "$SEP"
ps aux | grep -E "VBoxHeadless|VirtualBox|vboxmanage" | grep -v grep || echo "  Ningún proceso VirtualBox en ejecución"

echo ""
echo "$SEP"
echo " VMs registradas en VBoxManage"
echo "$SEP"
if command -v VBoxManage &>/dev/null; then
    VBoxManage list vms 2>&1
    echo ""
    echo "── En ejecución:"
    VBoxManage list runningvms 2>&1
else
    echo "  VBoxManage no disponible"
fi

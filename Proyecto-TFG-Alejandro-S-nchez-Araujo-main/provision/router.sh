#!/bin/bash

#  Clinica General -- Aprovisionamiento del Router
#  Ruta del archivo: provision/router.sh
#  Conecta LAN2 (10.0.50.0/23) con LAN1 (10.0.60.0/23)


echo " Actualizando sistema "
apt-get update -y
apt-get upgrade -y

echo " Activando reenvio de paquetes "

# Activar ip_forward de forma permanente
echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf
sysctl -p

echo " Configurando iptables"

# Permitir reenvio entre las dos redes
iptables -A FORWARD -i eth1 -o eth2 -j ACCEPT
iptables -A FORWARD -i eth2 -o eth1 -j ACCEPT

# NAT para que el trafico salga correctamente
iptables -t nat -A POSTROUTING -o eth1 -j MASQUERADE
iptables -t nat -A POSTROUTING -o eth2 -j MASQUERADE

# Guardar reglas para que persistan tras reinicio
apt-get install -y iptables-persistent
netfilter-persistent save

echo ""

echo "  Router listo"
echo "  eth1: 10.0.50.40 (LAN2 - Balanceador)"
echo "  eth2: 10.0.60.40 (LAN1 - FTP y DNS)"
echo "  Reenvio activado entre ambas redes"

echo ""

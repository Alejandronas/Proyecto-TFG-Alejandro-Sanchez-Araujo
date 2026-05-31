#!/bin/bash

#  Clinica General -- Aprovisionamiento del Balanceador
#  Ruta del archivo: provision/balanceador.sh


echo " Actualizando sistema "
apt-get update -y
apt-get upgrade -y

echo "Instalando Nginx "
apt-get install -y nginx

echo " Configurando Servidor de balanceo de carga "

cat > /etc/nginx/sites-available/clinica <<'EOF'
upstream backends {
    ip_hash;
    server 10.0.30.10;
    server 10.0.30.20;
}

server {
    listen 80;
    server_name clinicageneral.local;

    location / {
        proxy_pass         http://backends;
        proxy_set_header   Host $host;
        proxy_set_header   X-Real-IP $remote_addr;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
EOF

ln -sf /etc/nginx/sites-available/clinica /etc/nginx/sites-enabled/clinica
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl restart nginx
systemctl enable nginx

echo " Configurando ruta hacia red Windows Server "

# Ruta estatica con IP ROUTE para llegar al active directory de windows
ip route add 10.0.60.0/23 via 10.0.50.40 || true

# Ruta para hacer permanente via netplan 
NETPLAN_FILE=$(ls /etc/netplan/*.yaml | head -1)
cat >> $NETPLAN_FILE <<'NETPLAN'
      routes:
        - to: 10.0.60.0/23
          via: 10.0.50.40
NETPLAN
netplan apply || true

echo ""
echo "  Balanceador listo en 10.0.50.10"
echo "  ip_hash: 10.0.30.10 y 10.0.30.20"
echo "  Ruta hacia 10.0.60.0/23 via 10.0.50.40"
echo ""
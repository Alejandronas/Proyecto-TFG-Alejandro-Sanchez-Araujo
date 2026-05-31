#!/bin/bash

#  Clínica General — Aprovisionamiento de los Backend
#  Ruta archivo: provision/backend.sh
#  Aprovisionamiento para Backend 1 y Backend 2

echo "Actualizando sistema"
apt-get update -y
apt-get upgrade -y

echo "Instalando Apache, PHP, cliente MySQL y cliente NFS"
apt-get install -y apache2 php libapache2-mod-php php-mysql mysql-client nfs-common

echo "Configurando Apache"
systemctl enable apache2
systemctl start apache2
a2enmod rewrite

cat > /etc/apache2/sites-available/clinica.conf <<EOF
<VirtualHost *:80>
    ServerName ${HOSTNAME_VM}
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/clinica_error.log
    CustomLog \${APACHE_LOG_DIR}/clinica_access.log combined
</VirtualHost>
EOF

a2ensite clinica.conf
a2dissite 000-default.conf

# Elimina el index.html por defecto de Apache
rm -f /var/www/html/index.html

# Montaje de la carpeta app desde el servidor NFS
echo "10.0.30.40:/srv/app  /var/www/html  nfs  defaults,_netdev  0  0" >> /etc/fstab
mount -a

systemctl restart apache2

echo "Verificando conexión con SGBD"
mysql -h 10.0.20.10 -u clinica_user -p1234 clinica -e "SHOW TABLES;" && \
  echo "Conexión con SGBD: OK" || \
  echo "AVISO: SGBD no disponible aún — levanta el SGBD primero"

echo ""
echo "  Backend listo: ${HOSTNAME_VM}"
echo ""


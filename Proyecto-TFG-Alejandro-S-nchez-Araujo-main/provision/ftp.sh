#!/bin/bash

#  Clinica General -- Aprovisionamiento del FTP
#  Ruta  del archivo: provision/ftp.sh


echo " Actualizando sistema "
apt-get update -y
apt-get upgrade -y

echo " Instalando vsftpd "
apt-get install -y vsftpd

echo " Configurando vsftpd "

# Copia de seguridad de la configuracion original
cp /etc/vsftpd.conf /etc/vsftpd.conf.bak

# configuracion completa
cat > /etc/vsftpd.conf <<'EOF'
listen=YES
listen_ipv6=NO

# Deshabilitar acceso anonimo

anonymous_enable=NO

# Permitir usuarios locales

local_enable=YES

# Permitir escritura
write_enable=YES

# Encerrar usuarios en su directorio home

chroot_local_user=YES
allow_writeable_chroot=YES

# Modo pasivo

pasv_enable=YES
pasv_min_port=40000
pasv_max_port=50000
pasv_address=10.0.60.20

# Log de conexiones

xferlog_enable=YES
xferlog_file=/var/log/vsftpd.log

# Seguridad

ftpd_banner=Servidor FTP Clinica General
EOF

echo " Creando grupos de departamento "

# Un grupo por departamento permite que varios usuarios compartan el mismo directorio
groupadd -f grp_medicos
groupadd -f grp_direccion
groupadd -f grp_administracion
groupadd -f grp_rrhh
groupadd -f grp_informatica
groupadd -f grp_enfermeria
groupadd -f grp_recepcion
groupadd -f grp_laboratorio

echo " Creando directorios FTP "

mkdir -p /srv/ftp/compartido
mkdir -p /srv/ftp/medicos
mkdir -p /srv/ftp/direccion
mkdir -p /srv/ftp/administracion
mkdir -p /srv/ftp/rrhh
mkdir -p /srv/ftp/informatica
mkdir -p /srv/ftp/enfermeria
mkdir -p /srv/ftp/recepcion
mkdir -p /srv/ftp/laboratorio

# Propietario root, grupo del departamento, permisos 2775:

chown root:grp_medicos        /srv/ftp/medicos
chown root:grp_direccion      /srv/ftp/direccion
chown root:grp_administracion /srv/ftp/administracion
chown root:grp_rrhh           /srv/ftp/rrhh
chown root:grp_informatica    /srv/ftp/informatica
chown root:grp_enfermeria     /srv/ftp/enfermeria
chown root:grp_recepcion      /srv/ftp/recepcion
chown root:grp_laboratorio    /srv/ftp/laboratorio
chmod 2775 /srv/ftp/medicos
chmod 2775 /srv/ftp/direccion
chmod 2775 /srv/ftp/administracion
chmod 2775 /srv/ftp/rrhh
chmod 2775 /srv/ftp/informatica
chmod 2775 /srv/ftp/enfermeria
chmod 2775 /srv/ftp/recepcion
chmod 2775 /srv/ftp/laboratorio
chmod 755  /srv/ftp/compartido

echo " Permitiendo shell nologin en vsftpd "
# vsftpd comprueba que el shell del usuario esté en /etc/shells.
# /usr/sbin/nologin impide acceso SSH pero sí permite autenticación FTP.
grep -qxF '/usr/sbin/nologin' /etc/shells || echo '/usr/sbin/nologin' >> /etc/shells

echo " Creando usuarios FTP "


crear_ftp() {
    local user=$1 dir=$2 grupo=$3
    useradd -M -d "$dir" -s /usr/sbin/nologin -G "$grupo" "$user" 2>/dev/null || true
    echo "$user:Clinica2025!" | chpasswd
    echo "    $user -> $dir"
}

# Medicos: se mantiene usuario generico porque el AD no crea medicos individuales
crear_ftp ftp_medicos /srv/ftp/medicos grp_medicos

# Direccion
crear_ftp director.general /srv/ftp/direccion grp_direccion
crear_ftp subdirector       /srv/ftp/direccion grp_direccion

# Administracion
crear_ftp jefe.administracion /srv/ftp/administracion grp_administracion
crear_ftp facturacion1         /srv/ftp/administracion grp_administracion

# RRHH
crear_ftp jefe.rrhh    /srv/ftp/rrhh grp_rrhh
crear_ftp rrhh.tecnico /srv/ftp/rrhh grp_rrhh

# Informatica
crear_ftp admin.sistema /srv/ftp/informatica grp_informatica
crear_ftp tecnico.it1   /srv/ftp/informatica grp_informatica
crear_ftp tecnico.it2   /srv/ftp/informatica grp_informatica

# Enfermeria
crear_ftp enf.ana    /srv/ftp/enfermeria grp_enfermeria
crear_ftp enf.carlos /srv/ftp/enfermeria grp_enfermeria

# Recepcion
crear_ftp recepcion1 /srv/ftp/recepcion grp_recepcion
crear_ftp recepcion2 /srv/ftp/recepcion grp_recepcion

# Laboratorio
crear_ftp lab.tecnico1 /srv/ftp/laboratorio grp_laboratorio
crear_ftp lab.tecnico2 /srv/ftp/laboratorio grp_laboratorio

echo " Configurando DNS "

# Desactivar systemd-resolved
systemctl disable systemd-resolved
systemctl stop systemd-resolved

# Eliminar enlace simbolico de resolv.conf
rm /etc/resolv.conf

# Apuntar directamente al DNS del Windows Server
echo "nameserver 10.0.60.10
search ClinicaGeneral.local" > /etc/resolv.conf

# Hacerlo inmutable para que no se sobreescriba
chattr +i /etc/resolv.conf

# Corregir permisos del archivo netplan
chmod 600 /etc/netplan/50-vagrant.yaml

echo " Iniciando vsftpd "
systemctl enable vsftpd
systemctl restart vsftpd

echo ""
echo "  FTP listo en 10.0.60.20"
echo "  DNS apuntando a 10.0.60.10"
echo "  Dominio: ClinicaGeneral.local"
echo "  Contrasena de todos los usuarios: Clinica2025!"
echo ""
echo "  Usuarios creados:"
echo "  ftp_medicos                -> /srv/ftp/medicos"
echo "  director.general           -> /srv/ftp/direccion"
echo "  subdirector                -> /srv/ftp/direccion"
echo "  jefe.administracion        -> /srv/ftp/administracion"
echo "  facturacion1               -> /srv/ftp/administracion"
echo "  jefe.rrhh                  -> /srv/ftp/rrhh"
echo "  rrhh.tecnico               -> /srv/ftp/rrhh"
echo "  admin.sistema              -> /srv/ftp/informatica"
echo "  tecnico.it1                -> /srv/ftp/informatica"
echo "  tecnico.it2                -> /srv/ftp/informatica"
echo "  enf.ana                    -> /srv/ftp/enfermeria"
echo "  enf.carlos                 -> /srv/ftp/enfermeria"
echo "  recepcion1                 -> /srv/ftp/recepcion"
echo "  recepcion2                 -> /srv/ftp/recepcion"
echo "  lab.tecnico1               -> /srv/ftp/laboratorio"
echo "  lab.tecnico2               -> /srv/ftp/laboratorio"


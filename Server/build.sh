#!/bin/bash
export LANG=C

function _config_selinux {
    echo "Process SELinux Policy"
    setsebool -P httpd_can_network_connect on
    semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/mail/logs(/.*)?"
    semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/mail/temp(/.*)?"
    chcon -R -t mail_home_rw_t /MDDATA/

    cp -R postfix/sql-scripts/ /etc/postfix/
    cp dovecot/dovecot-sql.conf /etc/dovecot/
}

function _config_database {
    echo "Process Database"
    echo 'CREATE DATABASE `vmail`;' | mysql -u root -h localhost
    mysql -u root -h localhost vmail < DB/db-tables.sql
    echo "INSERT INTO \`domain\` (\`virtual\`) VALUES ('${_DomainName}')"   | mysql -u root -h localhost vmail
}

function _install_package {
    dnf install -y epel-release &> /dev/null || echo "Install EPEL fail."
    dnf config-manager --set-enabled crb
    dnf install -y chrony opendkim opendkim-tools postfix dovecot spamassassin dovecot-pigeonhole dovecot-mysql postfix-mysql -y &> /dev/null || echo "Install packages fail."
    dnf install -y mod_ssl php-fpm php-gd php-xml php-json php-mbstring php-mysqli php-pdo php-intl php-pecl-zip mariadb-server policycoreutils-python-utils &> /dev/null || echo "Install packages fail."
}

function _config_firewall {
    firewall-cmd --permanent --add-service=https
    firewall-cmd --permanent --add-service=smtp
    firewall-cmd --permanent --add-service=imap
    firewall-cmd --reload
}

function _enable_service {
    systemctl enable opendkim
    systemctl enable postfix
    systemctl enable dovecot
    systemctl enable httpd mariadb php-fpm

    systemctl restart httpd mariadb php-fpm postfix dovecot opendkim
}

function _config_webmail {
    DB_PASS=$(tr -dc A-Za-z0-9_ </dev/urandom | head -c 16 ; echo '')
    echo "CREATE DATABASE webmail;" | mysql -u root -h localhost
    echo "GRANT ALL ON webmail.* TO 'wmail'@'localhost' IDENTIFIED BY '${DB_PASS}' WITH GRANT OPTION;" | mysql -u root -h localhost

    mysql -uwmail -p${DB_PASS} -h localhost webmail < ../mail/SQL/mysql.initial.sql

    mail_key=$(tr -dc A-Za-z0-9_ </dev/urandom | head -c 24 ; echo '')

    sed -i -e "s/@@DB_CONFIG_PASSWORD@@/${DB_PASS}/g" ${cw}/mail/config.inc.php
    sed -i -e "s/@@DES_KEY@@/${mail_key}/g" ${cw}/mail/config.inc.php

    cp ${cw}/mail/config.inc.php ../mail/config/

    mv ../mail/installer{,_disable}
    chmod 700 ../mail/installer_disable
    chown root:root ../mail/installer_disable
    
}

function _config_postfix {
    postconf -e "inet_interfaces = all"
    postconf -e "myorigin = ${_DomainName}"
    postconf -e "virtual_transport = dovecot"
    postconf -e "dovecot_destination_recipient_limit = 1"
    postconf -e "enable_original_recipient = no"
    postconf -e "message_size_limit = 20971520"
    postconf -e "virtual_mailbox_domains = proxy:mysql:/etc/postfix/sql-scripts/virtual-domains.cf"
    postconf -e "virtual_mailbox_base = /MDDATA/vmail"
    postconf -e "virtual_mailbox_maps = proxy:mysql:/etc/postfix/sql-scripts/virtual-users.cf"
    postconf -e "virtual_uid_maps = static:498"
    postconf -e "virtual_gid_maps = static:498"
    postconf -e "smtpd_recipient_restrictions = permit_mynetworks permit_sasl_authenticated reject_unauth_destination permit_mx_backup"
    postconf -e "smtpd_sasl_auth_enable = yes"
    postconf -e "broken_sasl_auth_clients = yes"
    postconf -e "smtpd_sasl_type = dovecot"
    postconf -e "smtpd_sasl_path = private/auth"
    postconf -e "smtpd_sasl_security_options = noanonymous"
    postconf -e "smtpd_use_tls = yes"
    postconf -e "smtp_use_tls = yes"
    postconf -e "tls_random_source = dev:/dev/urandom"
    postconf -e "smtpd_tls_cert_file = /etc/postfix/ssl/smtpd.crt"
    postconf -e "smtpd_tls_key_file = /etc/postfix/ssl/smtpd.key"
    postconf -e "virtual_alias_maps = proxy:mysql:/etc/postfix/sql-scripts/virtual-alias.cf"
    postconf -e "transport_maps = hash:/etc/postfix/transport, regexp:/etc/postfix/transport.regexp"
    postconf -e "smtpd_milters = inet:localhost:8891"
    postconf -e "non_smtpd_milters = inet:localhost:8891"
    postconf -e "milter_default_action = accept"
    postconf -e "smtpd_tls_exclude_ciphers = aNULL, eNULL, EXPORT, DES, RC4, MD5, PSK, aECDH, EDH-DSS-DES-CBC3-SHA, EDH-RSA-DES-CBC3-SHA, KRB5-DES, CBC3-SHA"
    postconf -e "smtpd_tls_dh1024_param_file = /etc/pki/dhparams.pem"
    postconf -e "yahoo_initial_destination_concurrency = 1" 
    postconf -e "yahoo_destination_concurrency_limit = 4" 
    postconf -e "yahoo_destination_recipient_limit = 2" 
    postconf -e "yahoo_destination_rate_delay = 1s" 
    postconf -e "inet_protocols = ipv4"
}

function _config_dkim {
    cat > /etc/opendkim.conf <<EOF
PidFile	/var/run/opendkim/opendkim.pid
Mode	sv
Syslog	yes
SyslogSuccess	yes
LogWhy	yes
UserID	opendkim:opendkim
Socket	inet:8891@localhost
Umask	002
Canonicalization	relaxed/relaxed
Selector	default
MinimumKeyBits 1024
KeyTable	/etc/opendkim/KeyTable
SigningTable refile:/etc/opendkim/SigningTable
ExternalIgnoreList	refile:/etc/opendkim/TrustedHosts
InternalHosts	refile:/etc/opendkim/TrustedHosts
MilterDebug 1
EOF

    cat >> /etc/opendkim/SigningTable <<EOF
*@${_DomainName} default._domainkey.${_DomainName}
EOF

    cat >> /etc/opendkim/KeyTable <<EOF
default._domainkey.${_DomainName} ${_DomainName}:default:/etc/opendkim/keys/${_DomainName}/default.private
EOF

    mkdir -p /etc/opendkim/keys/${_DomainName}/
    cd /etc/opendkim/keys/${_DomainName}
    opendkim-genkey -r -d ${_DomainName}
    chown opendkim:opendkim default.private
}

_DomainName=$1
if [ "${_DomainName}" == "" ]; then
  echo "NO _DomainName!"
  exit 1
fi

cw=$(pwd)

_install_package

groupadd -g 498 vmail; useradd -u 498 -g vmail -r vmail
mkdir -p /MDDATA/vmail; chown vmail.vmail /MDDATA/vmail; chmod 700 /MDDATA/vmail
mv /etc/dovecot/dovecot.conf /etc/dovecot/dovecot.conf.orig
cat > /etc/dovecot/dovecot.conf <<EOF
auth_mechanisms = plain
#mail_debug = yes
disable_plaintext_auth = no
first_valid_uid = 498
mail_home = /MDDATA/vmail/%d/%n
mail_location = maildir:/MDDATA/vmail/%d/%n:INDEX=/MDDATA/vmail/indexes/%d/%n
mbox_write_locks = fcntl
protocols = imap pop3 sieve
service auth {
  unix_listener /var/spool/postfix/private/auth {
    group = postfix
    mode = 0660
    user = postfix
  }
  unix_listener auth-userdb {
    group = vmail
    user = vmail
    mode = 0600
  }
  user = root
}
service managesieve-login {
  inet_listener sieve {
    port = 4190
  }
}
ssl_cert = </etc/pki/dovecot/certs/dovecot.pem
ssl_key = </etc/pki/dovecot/private/dovecot.pem
ssl_cipher_list=ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-DSS-AES128-GCM-SHA256:kEDH+AESGCM:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA:ECDHE-ECDSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA256:DHE-RSA-AES256-SHA256:DHE-DSS-AES256-SHA:DHE-RSA-AES256-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:AES128-SHA256:AES256-SHA256:AES128-SHA:AES256-SHA:AES:CAMELLIA:DES-CBC3-SHA:!aNULL:!eNULL:!EXPORT:!DES:!RC4:!MD5:!PSK:!aECDH:!EDH-DSS-DES-CBC3-SHA:!EDH-RSA-DES-CBC3-SHA:!KRB5-DES-CBC3-SHA
ssl_prefer_server_ciphers = yes
ssl_dh_parameters_length = 2048
mail_plugins = quota
protocol imap {
  mail_plugins = \$mail_plugins imap_quota
}
plugin {
  quota = maildir:User quota
  sieve_global_path = /MDDATA/vmail/globalsieverc
}
protocol lda {
  postmaster_address = support@esys.com.tw
  mail_plugins = sieve
  lda_mailbox_autocreate = yes
  lda_mailbox_autosubscribe = yes
}
userdb {
   args = /etc/dovecot/dovecot-sql.conf
   driver = sql
}

passdb {
  args = /etc/dovecot/dovecot-sql.conf
  driver = sql
}
EOF

systemctl restart dovecot

mkdir -p /etc/postfix/ssl
ln -s /etc/pki/dovecot/certs/dovecot.pem /etc/postfix/ssl/smtpd.crt
ln -s /etc/pki/dovecot/private/dovecot.pem /etc/postfix/ssl/smtpd.key

_config_postfix

touch /etc/postfix/alias /etc/postfix/transport /etc/postfix/virtual /etc/postfix/domains
cat >> /etc/postfix/transport <<EOF
# Yahoo (USA)
yahoo.com       yahoo:
ymail.com       yahoo:
rocketmail.com  yahoo:

# Yahoo (INTL)
yahoo.ae        yahoo:
yahoo.at        yahoo:
yahoo.be        yahoo:
yahoo.ca        yahoo:
yahoo.ch        yahoo:
yahoo.cn        yahoo:
yahoo.co.il     yahoo:
yahoo.co.in     yahoo:
yahoo.co.jp     yahoo:
yahoo.co.kr     yahoo:
yahoo.co.nz     yahoo:
yahoo.co.th     yahoo:
yahoo.co.uk     yahoo:
yahoo.co.za     yahoo:
yahoo.com.ar    yahoo:
yahoo.com.au    yahoo:
yahoo.com.br    yahoo:
yahoo.com.cn    yahoo:
yahoo.com.hk    yahoo:
yahoo.com.mx    yahoo:
yahoo.com.my    yahoo:
yahoo.com.ph    yahoo:
yahoo.com.sg    yahoo:
yahoo.com.tr    yahoo:
yahoo.com.tw    yahoo:
yahoo.com.vn    yahoo:
yahoo.cz        yahoo:
yahoo.de        yahoo:
yahoo.dk        yahoo:
yahoo.en        yahoo:
yahoo.es        yahoo:
yahoo.fi        yahoo:
yahoo.fr        yahoo:
yahoo.gr        yahoo:
yahoo.ie        yahoo:
yahoo.it        yahoo:
yahoo.nl        yahoo:
yahoo.no        yahoo:
yahoo.pl        yahoo:
yahoo.pt        yahoo:
yahoo.ro        yahoo:
yahoo.ru        yahoo:
yahoo.se        yahoo:
EOF
cat >> /etc/postfix/transport.regexp <<EOF
/yahoo(\.[a-z]{2,3}){1,2}\$/  yahoo:
EOF

cp /etc/postfix/master.{cf,cf.bak}
cat >> /etc/postfix/master.cf <<EOF
dovecot unix - n n - - pipe flags=DRhu user=vmail:vmail argv=/usr/libexec/dovecot/dovecot-lda -f \${sender} -d \${recipient}
yahoo unix - - n - - smtp -o syslog_name=postfix-yahoo
EOF

touch /etc/postfix/alias /etc/postfix/transport /etc/postfix/virtual /etc/postfix/domains
postmap /etc/postfix/alias
postmap /etc/postfix/transport
postmap /etc/postfix/transport.regexp
postmap /etc/postfix/virtual

_config_dkim

cd ${cw}
openssl dhparam -out /etc/pki/dhparams.pem 2048
systemctl start mariadb

_config_selinux

_config_database

_config_firewall

_enable_service

_config_webmail

echo "Copy files"
cp -R ../{mail,mgmt} /var/www/html/
restorecon -R /var/www/html/mail/
chown -R apache:apache /var/www/html/mail/{logs,temp}

echo "Config Password"
v_passwd=$(tr -dc A-Za-z0-9_ </dev/urandom | head -c 16 ; echo '')
echo "GRANT ALL ON vmail.* TO 'vmail'@'localhost' IDENTIFIED BY '${v_passwd}' WITH GRANT OPTION;" | mysql -u root -h localhost

sed -i -e "s/password=DB_PASSWORD/password=${v_passwd}/g" /etc/dovecot/dovecot-sql.conf
sed -i -e "s/password = DB_PASSWORD/password = ${v_passwd}/g" /etc/postfix/sql-scripts/virtual-alias.cf
sed -i -e "s/password = DB_PASSWORD/password = ${v_passwd}/g" /etc/postfix/sql-scripts/virtual-domains.cf
sed -i -e "s/password = DB_PASSWORD/password = ${v_passwd}/g" /etc/postfix/sql-scripts/virtual-users.cf
sed -i -e "s/@@DB_CONFIG_PASSWORD@@/${v_passwd}/g" /var/www/html/mgmt/config.php

echo "DKIM Key:"
cat  /etc/opendkim/keys/${_DomainName}/default.txt

echo "You need config php.ini:"
echo -e '\tdate.timezone = "Asia/Taipei"'
echo -e "\tupload_max_filesize = 15M"
echo -e "\tpost_max_size = 20M"

echo "You need set MariaDB root password using mysql_secure_installation"

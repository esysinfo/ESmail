<?php
$config['db_dsnw'] = 'mysql://wmail:@@DB_CONFIG_PASSWORD@@@localhost/webmail';
$config['log_driver'] = 'syslog';
$config['syslog_id'] = 'esys-esmail';
$config['default_host'] = 'localhost';
$config['smtp_port'] = 25;
$config['support_url'] = 'https://www.esys.com.tw/product-and-service/mailserver/';
$config['blankpage_url'] = './watermark.html';
$config['skin_logo'] = array(
    'login[favicon]' => 'images/favicon.ico',
    'login' => 'images/ESmail-logo.png',
    '[print]' => 'images/ESmail-logo.png',
    '*' => 'images/ESmail-logo-w.png'
);
$config['des_key'] = '@@DES_KEY@@';
$config['product_name'] = 'ESYS Webmail';
$config['plugins'] = array('managesieve', 'vcard_attachments', 'zipdownload');
$config['language'] = 'zh_TW';
$config['create_default_folders'] = true;
$config['enable_installer'] = false; 
$config['skin'] = 'elastic';
$config['dont_override'] = array('skin');
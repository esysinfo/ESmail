# ESYS ESmail

![image](mgmt/images/ESmail-logo.png)

## 環境需求

* Almalinux 8
* 預先安裝校時系統
* 資料會放在 /MDDATA/ 中，請預先分配
* 所有需要對外的 Port 如下:

    * TCP/443
    * TCP/25
    * TCP/143

* DNS 對應
    * MX
    * A
    * DKIM 將於安裝後可取得

## 安裝流程

1. 將本套件上傳至主機中，完成後解開
2. 安裝流程

    ```
    # cd Server
    # chmod +x build.sh
    # ./build {DOMAIN}.sh
    ```
3. 修改 `/var/www/html/mgmt/config.php` 

    找到如下 PHP 原始碼參數並修改:
    
    ```
    $_admin_password = "HASHED_PASSWORD";
    ```

    > 可以使用 PHP 的 `crypt()` 結果取得


4. 安裝合法憑證，使用下列方式取得 Web 憑證位置

    ```=
    # grep crt /etc/httpd/conf.d/ssl.conf
    # grep key /etc/httpd/conf.d/ssl.conf
    ```

    將上述取得的檔案內容改為合法憑證內容

## 用戶端軟體資訊

* SMTP

    * Host: Mail 主機名稱
    * 協定: `STARTTLS`
    * Port: `25`
    * Login: `user@esys-example.com`
    * Password: 設定的密碼

* IMAP

    * Host: Mail 主機名稱
    * 協定: `STARTTLS`
    * Port: `143`
    * Login: `user@esys-example.com`
    * Password: 設定的密碼

* 用戶修改密碼: `https://web.esys-example.com/mgmt/chpass.php`

## 管理者介面

網址: `https://web.esys-example.com/mgmt/admin.php`

## Webmail 安裝

本包套件包含 Roundcube Webmail 1.4.13 版本，安裝流程如下:

1. 開啟 `https://web.esys-example.com/mail/`
2. 使用完整帳號登入 Wbmail
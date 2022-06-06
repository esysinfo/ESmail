<?php


class ChangePassword {

    private $db;
    private $comm;

    function __construct() {
        global $_db;

        $dsn = "mysql:host={$_db['host']};dbname={$_db['dbname']}";
        $db = new PDO($dsn, $_db['user'], $_db['pass']);
        $db->exec("set names utf8");

        $this->db = $db;

        require_once 'Common.php';
        $this->comm = new Common();
    }

    private function get_password_hash ($username = ""): array {
        $db = $this->db;
        // get hash password by username
        $sql_str = " SELECT `ID`, `password` FROM `mail` WHERE 1 = 1 ";
        $sql_str .= " AND `account` = :account ";

        $stmt = $db->prepare($sql_str);
        $stmt->bindParam(':account', $username, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $ret = $stmt->fetch(PDO::FETCH_ASSOC);

            return $this->comm->show_result(true, 'FOUND', $ret);
        } else {
            return $this->comm->show_result(false, $detail = $stmt->errorInfo());
        }
    }

    public function hash_new_password ($new_password = ""): string {
        if ($new_password == "")
            return "";

        return crypt($new_password, $ee_salt);
    }

    public function validate_pw ($user_name = "", $input_pass = ""): bool {
        $orig_passwd = $this->get_password_hash($user_name);
        if ($orig_passwd['status'])
            $orig_passwd = $orig_passwd['description']['detail']['password'];

        if ($orig_passwd == "")
            return false;

        if (crypt($input_pass, $orig_passwd) == $orig_passwd)
            return true;

        return false;
    }

    private function password_strength ($password = ""): array {
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
            return $this->comm->show_result(false, "PASSWORD_NOT_STRONG",
                "密碼必須包含:\n- 1 個小寫英文字母\n- 1 個大寫英文字母\n- 1 個數字\n- 1 個特殊符號\n- 大於等於 8 個字符\n");
        }else{
            return $this->comm->show_result(true);
        }
    }

    public function update_password ($user_name = "", $orig_password = "", $new_password = ""): array {

        $db = $this->db;
        global $passwd_alive_date;

        $pw_strong = $this->password_strength($new_password);
        if (! $pw_strong['status'])
            return $pw_strong;

        $user_info = $this->get_password_hash($user_name);
        if ($user_info['status']) $user_info = $user_info['description']['detail'];
        if ($user_info['ID'] == "")
            return $this->comm->show_result(false, "NO_USER_ID");

        $chk_pw = $this->validate_pw($user_name, $orig_password);
        if (!$chk_pw) {
            return $this->comm->show_result(false, "PWD_VALIDATE_FAIL");
        } else {
            // 新密碼不可以和舊密碼相同
            if ($orig_password == $new_password)
                return $this->comm->show_result(false, "PWD_SAME_AS_OLD_PASSWORD");
        }

        $new_hash_pw = $this->hash_new_password($new_password);
        if ($new_hash_pw == "")
            return $this->comm->show_result(false, "PWD_HASH_FAIL");

        $password_expire_date = date('Y-m-d',strtotime("+${passwd_alive_date} days"));
        $sql_str = "UPDATE `mail` SET `password` = :password, `expire_date` = :expire_date WHERE 1 = 1 ";
        $sql_str .= " AND `id` = :UID";

        $stmt = $db->prepare($sql_str);
        $stmt->bindParam(':UID', $user_info['ID'], PDO::PARAM_INT);
        $stmt->bindParam(':password', $new_hash_pw, PDO::PARAM_STR);
        $stmt->bindParam(':expire_date', $password_expire_date, PDO::PARAM_STR);

        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1)
                return $this->comm->show_result(true, 'MODIFY_OK');
            else
                return $this->comm->show_result(false, "NO_MODIFY");
        } else {
            return $this->comm->show_result(false, $detail = $stmt->errorInfo());
        }
    }
}
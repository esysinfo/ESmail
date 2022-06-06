<?php

class ADMIN {

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

    public function user_list ($uid = 0, $user_name = ""): array {
        $db = $this->db;

        $sql_str = "SELECT m.`ID`, m.`account`, m.`status`, m.`limit`, d.`virtual`, m.`expire_date` FROM `mail` m ";
        $sql_str .= " LEFT JOIN `domain` d ON m.`domain_id` = d.`ID` ";

        if ($uid != 0)
            $sql_str .= " AND m.`ID` = :uid ";
        if ($user_name != 0)
            $sql_str .= " AND m.`account` = :account ";

        $stmt = $db->prepare($sql_str);
        $stmt->bindParam(':account', $user_name, PDO::PARAM_STR);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    public function update_password ($uid = 0, $new_password = ""): array {

        $db = $this->db;

        $new_hash_pw = $this->hash_new_password($new_password);
        if ($new_hash_pw == "")
            return $this->comm->show_result(false, "PWD_HASH_FAIL");

        $sql_str = "UPDATE `mail` SET `password` = :password WHERE 1 = 1 ";
        $sql_str .= " AND `id` = :UID";

        $stmt = $db->prepare($sql_str);
        $stmt->bindParam(':UID', $uid, PDO::PARAM_INT);
        $stmt->bindParam(':password', $new_hash_pw, PDO::PARAM_STR);

        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1)
                return $this->comm->show_result(true, 'MODIFY_OK');
            else
                return $this->comm->show_result(false, "NO_MODIFY");
        } else {
            return $this->comm->show_result(false, $detail = $stmt->errorInfo());
        }
    }

    public function update_status ($uid = 0, $status = 1) {
        $db = $this->db;

        $sql_str = "UPDATE `mail` SET `status` = :status WHERE 1 = 1 ";
        $sql_str .= " AND `ID` = :uid";

        $stmt = $db->prepare($sql_str);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1)
                return $this->comm->show_result(true, 'MODIFY_OK');
            else
                return $this->comm->show_result(false, "NO_MODIFY");
        } else {
            return $this->comm->show_result(false, $detail = $stmt->errorInfo());
        }
    }

    public function update_expire_date ($uid = 0, $expire_date = "") {
        $db = $this->db;

        $sql_str = "UPDATE `mail` SET `expire_date` = :expire_date WHERE 1 = 1 ";
        $sql_str .= " AND `ID` = :uid";

        $stmt = $db->prepare($sql_str);
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->bindParam(':expire_date', $expire_date, PDO::PARAM_STR);

        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1)
                return $this->comm->show_result(true, 'MODIFY_OK');
            else
                return $this->comm->show_result(false, "NO_MODIFY");
        } else {
            return $this->comm->show_result(false, $detail = $stmt->errorInfo());
        }
    }

    public function add_account ($account = "", $password = "", $limit = 2, $domain_id = 0) {
        $db = $this->db;

        $hashed_str = $this->hash_new_password($password);

        $sql_str = " INSERT INTO `mail` (`account`, `password`, `limit`, `domain_id`)";
        $sql_str .= " VALUES (:account, :password, :limit, :domain_id) ;";

        $stmt = $db->prepare($sql_str);
        $stmt->bindParam(':account', $account, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashed_str, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':domain_id', $domain_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->comm->show_result(true);
        } else {
            return $this->comm->show_result(false, $detail = $stmt->errorInfo());
        }

    }

    private function check_string ($string = ""): string {
        $string = trim(preg_replace('/\s+/', '', $string));
            return $string;
    }

    public function get_alias ($aid = 0) {
        $db = $this->db;

        $sql_str = " SELECT `ID`, `destination`, `alias` FROM `alias` WHERE 1 = 1 ";
        if ($aid != 0)
            $sql_str .= " AND `ID` = :aid ";

        $stmt = $db->prepare($sql_str);
        $stmt->bindParam(':aid', $aid, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->comm->show_result(true, "", $detail = $stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            return $this->comm->show_result(false, $detail = $stmt->errorInfo());
        }
    }

    public function add_alias ($alias = "", $destination = ""): array {
        $db = $this->db;

        $alias = $this->check_string($alias);
        $destination = $this->check_string($destination);

        if ($alias != "" && $destination != "") {
            $sql_str = "INSERT INTO `alias` (`alias`, `destination`) VALUES (:alias, :destination);";

            $stmt = $db->prepare($sql_str);
            $stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
            $stmt->bindParam(':destination', $destination, PDO::PARAM_STR);

            if ($stmt->execute()) {
                return $this->comm->show_result(true);
            } else {
                return $this->comm->show_result(false, $detail = $stmt->errorInfo());
            }
        } else {
            return $this->comm->show_result(false, "VALUES_EMPTY");
        }
    }

    public function save_alias ($alias_id = 0, $alias = "", $destination = ""): array {
        $db = $this->db;

        $alias = $this->check_string($alias);
        $destination = $this->check_string($destination);

        if ($alias != "" && $destination != "") {
            $sql_str = "UPDATE `alias` SET `alias` = :alias,  `destination` = :destination WHERE 1 = 1 ";
            $sql_str .= " AND `ID` = :aid; ";

            $stmt = $db->prepare($sql_str);
            $stmt->bindParam(':aid', $alias_id, PDO::PARAM_INT);
            $stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
            $stmt->bindParam(':destination', $destination, PDO::PARAM_STR);

            if ($stmt->execute()) {
                return $this->comm->show_result(true);
            } else {
                return $this->comm->show_result(false, $detail = $stmt->errorInfo());
            }
        } else {
            return $this->comm->show_result(false, "VALUES_EMPTY");
        }
    }

    public function remove_alias ($alias_id = ""): array {

        $db = $this->db;

        $sql_str = "DELETE FROM `alias` WHERE `ID` = :aid";

        $stmt = $db->prepare($sql_str);
        $stmt->bindParam(':aid', $alias_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->comm->show_result(true);
        } else {
            return $this->comm->show_result(false, $detail = $stmt->errorInfo());
        }
    }

    public function get_domain ($domain_name = ""): array {
        $db = $this->db;

        $sql_str = "SELECT `ID`, `virtual` FROM `domain` WHERE 1 = 1 ";
        if ($domain_name != "")
            $sql_str .= " AND `virtual` = :domain ";

        $stmt = $db->prepare($sql_str);
        if ($domain_name != "")
            $stmt->bindParam(':domain', $domain_name, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->comm->show_result(true, 'FOUND', $ret);
        } else {
            return $this->comm->show_result(false, $detail = $stmt->errorInfo());
        }
    }

    public function set_domain ($domain_name = ""): array {
        $db = $this->db;
        $domains = $this->get_domain();

        if ($domains['status']) {
            if (count($domains['description']['detail']) == 1) {
                var_dump($domains['description']['detail'][0]);
                $domain_id = $domains['description']['detail'][0]['ID'];
                $sql_str = "UPDATE `domain` SET `virtual` = :domain_name WHERE `ID` = :domain_id; ";
                $stmt = $db->prepare($sql_str);
                $stmt->bindParam(':domain_id', $domain_id, PDO::PARAM_INT);
                $stmt->bindParam(':domain_name', $domain_name, PDO::PARAM_STR);
            }

            if ($stmt->execute()) {
                return $this->get_domain($domain_name);
            } else {
                return $this->comm->show_result(false, $detail = $stmt->errorInfo());
            }
        } else {
            return $this->comm->show_result(false, $detail = $domains);
        }
    }
}
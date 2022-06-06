<?php
include_once '../config.php';
include_once '../include/Common.php';
include_once '../include/ADMIN.php';

session_start();

if (@$_SESSION['role'] != "admin") {
    die((new Common())->show_json_result(false, "ADMIN_NO_LOGIN"));
}

$act = @$_POST['cmd'];

switch ($act) {
    case "UPDATE_PASSWORD":
        $uid = @$_POST['args']['uid'];
        $new_passwd = @$_POST['args']['new_pw'];

        if ($new_passwd == "")
            die((new Common())->show_json_result(false, "NO_PASSWORD"));

        echo json_encode((new ADMIN())->update_password($uid, $new_passwd));

        exit;
    break;

    case "UPDATE_STATUS":
        $uid = @$_POST['args']['uid'];
        $status = @$_POST['args']['status'];

        echo json_encode((new ADMIN())->update_status($uid, $status));

        exit;

    break;

    case "UPDATE_EXPIRE_DATE":
        $uid = @$_POST['args']['uid'];
        $expire_date = @$_POST['args']['expire_date'];

        if ($uid == "" || $expire_date == "") {
            echo json_encode((new Common())->show_json_result(false, "UPDATE_EXPIRE_DATE_FAIL"));
            exit;
        } else {
            $uid = trim($uid);
            $expire_date = trim($expire_date);
        }

        echo json_encode((new ADMIN())->update_expire_date($uid, $expire_date));

        exit;

    break;

    case "ADD_ACCOUNT":
        $account = @$_POST['args']['account'];
        $password = @$_POST['args']['password'];
        $limit = 2;
        $domain_id = $_domain_id;

        if ($account == "" || $password == "") {
            echo json_encode((new Common())->show_json_result(false, "NO_ACCOUNT_PASSWORD"));
            exit;
        } else {
            $account = trim($account);
            $password = trim($password);
        }

        if (!(new Common())->check_accountallow_letter($account)) {
            echo json_encode((new Common())->show_json_result(false, "NOT_ALLOW_LETTER"));
            exit;
        }

        echo json_encode((new ADMIN())->add_account($account, $password, $limit, $domain_id));

        exit;

        break;

    case "ADD_ALIAS":
        $alias = @$_POST['args']['alias'];
        $destination = @$_POST['args']['destination'];

        if ($alias == "" || $destination == "") {
            echo json_encode((new Common())->show_json_result(false, "NO_ALIASES_DESTINATION"));
            exit;
        } else {
            $alias = trim($alias);
            $destination = trim($destination);

            foreach ($dest as $v) {
                if (!(new Common())->check_allow_letter($v)) {
                    echo json_encode((new Common())->show_json_result(false, "{$v}DEST_NOT_ALLOW_LETTER"));
                    exit;
                }
            }

            if (!(new Common())->check_allow_letter($alias)) {
                echo json_encode((new Common())->show_json_result(false, "NOT_ALLOW_LETTER"));
                exit;
            }
        }

        echo json_encode((new ADMIN())->add_alias($alias, $destination));

        exit;

        break;

    case "SAVE_ALIAS":
        $alias = @$_POST['args']['alias'];
        $destination = @$_POST['args']['destination'];
        $alias_id = @$_POST['args']['aid'];

        $dest = explode(",", $destination);
        foreach ($dest as $v) {
            if (!(new Common())->check_allow_letter($v)) {
                echo json_encode((new Common())->show_json_result(false, "{$v}DEST_NOT_ALLOW_LETTER"));
                exit;
            }
        }

        if (!(new Common())->check_allow_letter($alias)) {
            echo json_encode((new Common())->show_json_result(false, "SRC_NOT_ALLOW_LETTER"));
            exit;
        } else {
            echo json_encode((new ADMIN())->save_alias($alias_id, $alias, $destination));

            exit;
        }

        break;

    case "REMOVE_ALIAS":
        $alias_id = @$_POST['args']['aid'];

        echo json_encode((new ADMIN())->remove_alias($alias_id));

        exit;

        break;
    case "SET_DOMAIN":
        $domain_name = @$_POST['args']['domain_name'];

        echo json_encode((new ADMIN())->set_domain($domain_name));

        exit;
        break;
}

(new Common())->show_json_result(false, "NO_DATA");
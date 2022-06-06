<?php
$act = @$_POST['cmd'];

require_once '../config.php';
require_once '../include/Common.php';
require_once '../include/ChangePassword.php';

switch ($act) {
    case "UPDATE_PASSWORD":
        $user_name = @$_POST['args']['user_name'];
        $old_passwd = @$_POST['args']['old_pw'];
        $new_passwd = @$_POST['args']['new_pw'];
        $new_passwd_cfm = @$_POST['args']['new_pw_cfm'];

        if ($new_passwd != $new_passwd_cfm) {
            die((new Common())->show_json_result(false, "NEW_PASSWORD_NOT_MATCH"));
        }


        echo json_encode((new ChangePassword())->update_password($user_name, $old_passwd, $new_passwd));

        exit;
    break;

}

(new Common())->show_json_result(false, "NO_DATA");
<?php
require_once "../config.php";
require_once '../include/Common.php';
require_once '../include/ADMIN.php';

session_start();

if (@$_SESSION['role'] != "admin") {
    header("Location: ./admin-login.php");
}

$adm = new ADMIN();
$domains = $adm->get_domain();

?><!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="../images/favicon.ico">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Webmail Admin</title>
</head>
<body>
<?php include_once "navbar.html"; ?>
<main role="main" class="container">
    <div class="row">
        <div class="col-12">
            <h1>新增帳號 @<?php echo $domains['description']['detail'][0]['virtual']; ?></h1>
            <table class="table table-bordered table-hover" id="tb_new_account">
                <tr>
                    <th>帳號</th>
                    <td>
                        <input id="account" class="form-control" type="text" value="" placeholder="輸入帳號" maxlength="25" />
                    </td>
                </tr>
                <tr>
                    <th>密碼</th>
                    <td><input id="password" class="form-control" type="text" value="" placeholder="輸入密碼" maxlength="25" /></td>
                </tr>
            </table>
            <input type="button" value="新增帳號" onClick="add_account();" class="btn btn-success form-control" />
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h1>信箱列表</h1>
            <?php
            require_once "../include/ADMIN.php";
            $mail_list = (new ADMIN())->user_list();
            if ($mail_list['status'] == false) {
                echo "取得列表發生錯誤!";
            } else {
                $mail_list = $mail_list['description']['detail'];
            }
            ?>
            <table class="table table-bordered table-hover" id="pwd_form">
                <thead>
                <tr>
                    <th>序號#</th>
                    <th>帳號</th>
                    <th>Domain</th>
                    <th>狀態</th>
                    <th>到期日</th>
                    <th>密碼</th>
                    <!--th>可用空間</th-->
                </tr>
                </thead>
                <tbody>
                <?php foreach ($mail_list as $idx => $mk) { ?>
                <tr>
                    <td><span><?php echo $idx + 1; ?></span></td>
                    <td><span><?php echo $mk['account']; ?></span></td>
                    <td><span><?php echo $mk['virtual']; ?></span></td>
                    <td>
                        <select id="status" class="form-control" onChange="update_status(<?php echo $mk['ID']; ?>, this);">
                            <option value="1" <?php if ($mk['status'] == 1) echo "selected"; ?>>啟用</option>
                            <option value="0" <?php if ($mk['status'] == 0) echo "selected"; ?>>停用</option>
                        </select>
                    </td>
                    <td><input type="date" class="form-control" id="expire_date" name="expire_date" onblur="update_expire_date(<?php echo $mk['ID']; ?>, this);" value="<?php echo $mk['expire_date']; ?>" /></td>
                    <td><input type="button" value="修改密碼" onClick="update_password(<?php echo $mk['ID']; ?>);" class="btn btn-warning" /></td>
                    <!--td><span><?php echo $mk['limit']; ?></span></td-->
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js">
</script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js">
</script>
<script language="JavaScript">
    function add_account () {
        const tb = $("#tb_new_account");
        const user_args = {
            'cmd': 'ADD_ACCOUNT',
            'args': {
                'account': $(tb).find("#account").val(),
                'password': $(tb).find("#password").val(),
            }
        };

        $.post('../api/adm.php', user_args, function (ret) {
            let r;
            try {
                r = JSON.parse(ret);
            } catch (e) {
                console.log(e);
            }

            if (!r.status) {
                console.log(r);
                alert("修改失敗！");
            } else {
                window.location.href = window.location.href;
            }
        }, "text");
    }

    function update_expire_date (uid, obj) {
        const expire_date = $(obj).val();
        const user_args = {
            'cmd': 'UPDATE_EXPIRE_DATE',
            'args': {
                'uid': uid,
                'expire_date': expire_date
            }
        };

        alert(user_args.args.expire_date);

        $.post('../api/adm.php', user_args, function (ret) {
            let r;
            try {
                r = JSON.parse(ret);
            } catch (e) {
                console.log(e);
            }

            if (!r.status) {
                console.log(r);
                alert("修改失敗！");
            } else {
                window.location.href = window.location.href;
            }
        }, "text");
    }

    function update_status (uid, obj) {
        const status = $(obj).val();
        const user_args = {
            'cmd': 'UPDATE_STATUS',
            'args': {
                'uid': uid,
                'status': status
            }
        };

        $.post('../api/adm.php', user_args, function (ret) {
            let r;
            try {
                r = JSON.parse(ret);
            } catch (e) {
                console.log(e);
            }

            if (!r.status) {
                console.log(r);
                alert("修改失敗！");
            } else {
                window.location.href = window.location.href;
            }
        }, "text");
    }

    function update_password (uid) {
        const new_password = prompt("Please input password: ");
        if (typeof(new_password) == "undefined")
            return false;
        else if (new_password == "")
            return false;

        const user_args = {
            'cmd': 'UPDATE_PASSWORD',
            'args': {
                'uid': uid,
                'new_pw': new_password
            }
        };

        $.post('../api/adm.php', user_args, function (ret) {
            let r;
            try {
                r = JSON.parse(ret);
            } catch (e) {
                console.log(e);
            }

            if (r.status) {
                alert("修改成功！");
                window.location.href = window.location.href;
            } else {
                console.log(r);
                alert("修改失敗！");
            }
        }, "text");
    }
</script>
<script>
$('#pwd_form').DataTable();
</script>
</body>
</html>
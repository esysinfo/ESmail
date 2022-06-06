<?php
require_once "../config.php";

session_start();

if (@$_SESSION['role'] != "admin") {
    header("Location: ./admin-login.php");
}
?><!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="../images/favicon.ico">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Webmail Admin Alias</title>
</head>
<body>
<?php include_once "navbar.html"; ?>
<main role="main" class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>新增別名</h1>
            <table class="table table-bordered table-hover" id="tb_new_alias">
                <tr>
                    <th>別名</th>
                    <td><input id="alias" class="form-control" type="text" value="" placeholder="輸入完整別名，如 user@example.com" /></td>
                </tr>
                <tr>
                    <th>目標</th>
                    <td><input id="destination" class="form-control" type="text" value="" placeholder="輸入目標，多個目標以逗號相隔。" /></td>
                </tr>
            </table>
            <input type="button" value="新增" onClick="add_alias();" class="btn btn-success form-control" />
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h1>別名列表</h1>
            <?php
            require_once "../include/ADMIN.php";
            $mail_list = (new ADMIN())->get_alias();
            if ($mail_list['status'] == false) {
                echo "取得列表發生錯誤!";
            } else {
                $mail_list = $mail_list['description']['detail'];
            }
            ?>
            <table class="table table-bordered table-hover" id="tb_list">
                <thead>
                <tr>
                    <th>序號#</th>
                    <th>別名</th>
                    <th>目標</th>
                    <th>移除</th>
                    <!--th>可用空間</th-->
                </tr>
                </thead>
                <tbody>
                <?php foreach ($mail_list as $idx => $mk) { ?>
                    <tr id="tr_<?php echo $mk['ID']; ?>">
                        <td><span><?php echo $idx + 1; ?></span></td>
                        <td>
                            <input type="text" class="form-control" id="alias" value="<?php echo $mk['alias']; ?>" />
                        </td>
                        <td>
                            <input type="text" class="form-control" id="destination" value="<?php echo $mk['destination']; ?>" />
                        </td>
                        <td>
                            <input type="button" class="btn btn-success" onClick="save_alias(<?php echo $mk['ID']; ?>);" value="儲存" />
                            <input type="button" class="btn btn-danger" onClick="remove_alias(<?php echo $mk['ID']; ?>);" value="移除" />
                        </td>
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
<script language="JavaScript">
    function save_alias (aid) {
        const tb = $("#tb_list");
        const user_args = {
            'cmd': 'SAVE_ALIAS',
            'args': {
                'alias': $(tb).find("#tr_" + aid).find("#alias").val(),
                'destination': $(tb).find("#tr_" + aid).find("#destination").val(),
                'aid': aid
            }
        };

        $.post('../api/adm.php', user_args, function (r) {
            try {
                if (!r.status) {
                    console.log(r);
                    alert("設定失敗！");
                } else {
                    location.reload();
                }
            } catch (e) {
                console.log(e);
            }
        }, "json");
    }

    function add_alias () {
        const tb = $("#tb_new_alias");
        const user_args = {
            'cmd': 'ADD_ALIAS',
            'args': {
                'alias': $(tb).find("#alias").val(),
                'destination': $(tb).find("#destination").val()
            }
        };

        $.post('../api/adm.php', user_args, function (r) {
            try {
                if (!r.status) {
                    console.log(r);
                    alert("修改失敗！");
                } else {
                    location.reload();
                }
            } catch (e) {
                console.log(e);
            }
        }, "json");
    }

    function remove_alias (aid) {
        const user_args = {
            'cmd': 'REMOVE_ALIAS',
            'args': {
                'aid': aid
            }
        };

        $.post('../api/adm.php', user_args, function (r) {
            try {
                if (!r.status) {
                    console.log(r);
                    alert("刪除失敗！");
                } else {
                    location.reload();
                }
            } catch (e) {
                console.log(e);
            }
        }, "json");
    }
</script>
</body>
</html>
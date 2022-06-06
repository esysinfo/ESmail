<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="images/favicon.ico">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Webmail</title>
</head>
<body>
<?php include_once "navbar.html"; ?>
<main role="main" class="container">
    <div class="row">
        <div class="col-md-5 offset-md-4">
            <h1 class="mt-3">修改個人密碼</h1>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-6 offset-md-3">
            <div class="row">
            <table class="table table-bordered table-hover" id="pwd_form">
                <tr>
                    <th>帳號</th>
                    <td><input type="text" class="form-control" id="user_name" placeholder="輸入帳號" maxlength="50" /></td>
                </tr>
                <tr>
                    <th>原始密碼</th>
                    <td><input type="password" class="form-control" id="old_password" placeholder="原始密碼" maxlength="20" /></td>
                </tr>
                <tr>
                    <th>新密碼</th>
                    <td><input type="password" class="form-control" id="new_password" placeholder="輸入新密碼" maxlength="20" /></td>
                </tr>
                <tr>
                    <th>確認新密碼</th>
                    <td><input type="password" class="form-control" id="new_password_confirm" placeholder="確認新密碼" maxlength="20" /></td>
                </tr>
            </table>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <input type="button" class="form-control btn-success" value="確定修改" onClick="update_password();" />
                </div>
                <div class="col-md-6">
                    <input type="button" class="form-control btn-warning" value="取消修改" onClick="javascript:location.replace('/');" />
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script language="JavaScript">
    function update_password () {
        const frm = $("#pwd_form");
        const user_args = {
            'cmd': 'UPDATE_PASSWORD',
            'args': {
                'user_name': $(frm).find("#user_name").val(),
                'old_pw': $(frm).find("#old_password").val(),
                'new_pw': $(frm).find("#new_password").val(),
                'new_pw_cfm': $(frm).find("#new_password_confirm").val()
            }
        };

        $.post('./api/', user_args, function (ret) {
            let r;
            try {
                r = JSON.parse(ret);
            } catch (e) {
                console.log(e);
            }

            if (r.status) {
                alert("修改成功！");
                location.replace("../");
            } else {
                console.log(r);
                if (r.description.message == "PASSWORD_NOT_STRONG")
                    alert(r.description.detail);
                else if (r.description.message == "PWD_SAME_AS_OLD_PASSWORD")
                    alert("新密碼不可舊密碼相同。");
                else
                    alert("修改失敗！");
            }
        }, "text");
    }
</script>
</body>
</html>
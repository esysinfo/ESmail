<?php
session_start();
require_once "../config.php";

if (@$_SESSION['role'] == "admin") {
    header("Location: ./admin.php");
} else {
    if (crypt($_POST['password'], $_admin_password) == $_admin_password) {
        $_SESSION['role'] = "admin";
        header("Location: ./admin.php");
    }
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

    <title>Webmail Admin Login</title>
</head>
<body>
<?php include_once "navbar.html"; ?>
<main role="main" class="container">
    <div class="row">
        <div class="col-md-5 offset-md-4">
            <h1 class="mt-3">Admin Login</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <form name="frm_login" id="frm_login" method="post" action="admin-login.php">
                <table class="table table-bordered table-hover" id="pwd_form">
                    <tr>
                        <th>密碼</th>
                    </tr>
                    <tr>
                        <td>
                            <input type="password" class="form-control" name="password" id="password" placeholder="輸入密碼" maxlength="20" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="col-md-6">
            <input type="button" class="form-control btn-success" onClick="login();" value="確定" />
        </div>
        <div class="col-md-6">
            <input type="button" class="form-control btn-warning" value="取消" onClick="javascript:location.replace('/');" />
        </div>
    </div>
</main>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script language="JavaScript">
    function login () {
        const password = $("#frm_login #user_name").val();
        if (password == "") return false;

        $("#frm_login").submit();
    }
</script>
</body>
</html>
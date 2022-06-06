<?php
require_once "../config.php";
require_once '../include/Common.php';
require_once '../include/ADMIN.php';

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

    <title>Webmail Domain</title>
</head>
<body>
<?php include_once "navbar.html"; ?>
<main role="main" class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>設定 Domain</h1>
            <?php
            $adm = new ADMIN();
            $domains = $adm->get_domain();
            
            if (count($domains['description']['detail']) > 1) {
                echo "授權錯誤";
                $domain_name = "";
            } else {
                if (@$domains['description']['detail'][0] == "")
                    $domain_name = "";
                else
                    $domain_name = $domains['description']['detail'][0]['virtual'];
            }
            ?>
            <table class="table table-bordered table-hover" id="tb_set_domain">
                <tr>
                    <th>Domain 名</th>
                    <td><input id="domain_name" class="form-control" type="text" value="<?php echo $domain_name; ?>" placeholder="example.com" /></td>
                </tr>
            </table>
            <input type="button" value="設定" onClick="set_domain();" class="btn btn-success form-control" />
        </div>
    </div>
</main>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script language="JavaScript">
    function set_domain () {
        const tb = $("#tb_set_domain");
        const user_args = {
            'cmd': 'SET_DOMAIN',
            'args': {
                'domain_name': $(tb).find("#domain_name").val()
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
</script>
</body>
</html>
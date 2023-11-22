<?php 
session_start();
require('connectDB.php');

if (isset($_POST['update'])) {

    $parentsemail = $_SESSION['Parents-email'];

    $up_name = $_POST['up_parents_name'];
    $up_email = $_POST['up_parents_email'];
    $up_password =$_POST['up_parents_pwd'];

    if (empty($up_name) || empty($up_email)) {
        header("location: index_users.php?error=emptyfields");
        exit();
    }
    elseif (!filter_var($up_email,FILTER_VALIDATE_EMAIL) && !preg_match("/^[a-zA-Z 0-9]*$/", $up_name)) {
        header("location: index_users.php?error=invalidEN&UN=".$up_name);
        exit();
    }
    elseif (!filter_var($up_email,FILTER_VALIDATE_EMAIL)) {
        header("location: index_users.php?error=invalidEN&UN=".$up_name);
        exit();
    }
    elseif (!preg_match("/^[a-zA-Z 0-9]*$/", $up_name)) {
        header("location: index_users.php?error=invalidName&E=".$up_email);
        exit();
    }
    else{
        $sql = "SELECT * FROM parents WHERE parents_email=?";  
        $result = mysqli_stmt_init($conn);
        if ( !mysqli_stmt_prepare($result, $sql)){
            header("location: index_users.php?error=sqlerror1");
            exit();
        }
        else{
            mysqli_stmt_bind_param($result, "s", $parentsemail);
            mysqli_stmt_execute($result);
            $resultl = mysqli_stmt_get_result($result);
            if ($row = mysqli_fetch_assoc($resultl)) {
                $pwdCheck = password_verify($up_password, $row['parents_pwd']);
                if ($pwdCheck == false) {
                    header("location: index_users.php?error=wrongpasswordup");
                    exit();
                }
                else if ($pwdCheck == true) {
                    if ($parentsemail == $up_email) {
                        $sql = "UPDATE parents SET parents_name=? WHERE parents_email=?";
                        $stmt = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($stmt, $sql)) {
                            header("location: index_users.php?error=sqlerror");
                            exit();
                        }
                        else{
                            mysqli_stmt_bind_param($stmt, "ss", $up_name, $parentsemail);
                            mysqli_stmt_execute($stmt);
                            $_SESSION['parents-name'] = $up_name;
                            header("location: index_users.php?success=updated");
                            exit();
                        }
                    }
                    else{
                        $sql = "SELECT parents_email FROM parents WHERE parents_email=?";  
                        $result = mysqli_stmt_init($conn);
                        if ( !mysqli_stmt_prepare($result, $sql)){
                            header("location: index_users.php?error=sqlerror1");
                            exit();
                        }
                        else{
                            mysqli_stmt_bind_param($result, "s", $up_email);
                            mysqli_stmt_execute($result);
                            $resultl = mysqli_stmt_get_result($result);
                            if (!$row = mysqli_fetch_assoc($resultl)) {
                                $sql = "UPDATE parents SET parents_name=?, parents_email=? WHERE parents_email=?";
                                $stmt = mysqli_stmt_init($conn);
                                if (!mysqli_stmt_prepare($stmt, $sql)) {
                                    header("location: index_users.php?error=sqlerror");
                                    exit();
                                }
                                else{
                                    mysqli_stmt_bind_param($stmt, "sss", $up_name, $up_email, $parentsemail);
                                    mysqli_stmt_execute($stmt);
                                    $_SESSION['parents-name'] = $up_name;
                                    $_SESSION['Parents-email'] = $up_email;
                                    header("location: index_users.php?success=updated");
                                    exit();
                                }
                            }
                            else{
                                header("location: index_users.php?error=nouser2");
                                exit();
                            }
                        }
                    }
                }
            }
            else{
                header("location: index_users.php?error=nouser1");
                exit();
            }
        }
    }
}
else{
    header("location: index_users.php");
    exit();
}
//*********************************************************************************
?>
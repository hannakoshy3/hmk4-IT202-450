<?php
require(__DIR__ . "/../../partials/nav.php");
reset_session();
?>

<body class="bg_img home">
    <div class="register-container">
        <div class="overlay banner register-banner">
            <div class="container-fluid">
                <h2>Register</h2>
                <form onsubmit="return validate(this)" method="POST">
                    <?php render_input(["type" => "email", "id" => "email", "name" => "email", "label" => "Email", "rules" => ["required" => true]]); ?>
                    <?php render_input(["type" => "text", "id" => "username", "name" => "username", "label" => "Username", "rules" => ["required" => true, "maxlength" => 30]]); ?>
                    <?php render_input(["type" => "password", "id" => "password", "name" => "password", "label" => "Password", "rules" => ["required" => true, "minlength" => 8]]); ?>
                    <?php render_input(["type" => "password", "id" => "confirm", "name" => "confirm", "label" => "Confirm Password", "rules" => ["required" => true, "minlength" => 8]]); ?>
                    <?php render_button(["text" => "Register", "type" => "submit"]); ?>
                </form>
            </div>
        </div>
    </div>
</body>


<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success
        let email = form.email.value;
        let username = form.username.value;
        let password = form.password.value;
        let confirm = form.confirm.value;

        const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        if (!emailPattern.test(email)) {
            flash(" [client] Please enter a valid email address");
            return false;
        }

        const usernamePattern = /^[a-zA-Z0-9_-]{3,15}$/;
        if (!usernamePattern.test(username)) {
            flash("[client] Username must only contain 3-15 characters a-z, 0-9, _, or -");
            return false;
        }

        if (password.length < 8) {
            flash("[client] Password must be at least 8 characters long");
            return false;
        }

        if (password !== confirm) {
            flash("[client] Passwords must match");
            return false;
        }




        return true;
    }
</script>
<?php
//TODO 2: add PHP Code
if (isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["confirm"]) && isset($_POST["username"])) {
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    $confirm = se($_POST, "confirm", "", false);
    $username = se($_POST, "username", "", false);
    //TODO 3
    $hasError = false;
    if (empty($email)) {
        flash("Email must not be empty", "danger");
        $hasError = true;
    }
    //sanitize
    $email = sanitize_email($email);
    //validate
    if (!is_valid_email($email)) {
        flash("Invalid email address", "danger");
        $hasError = true;
    }
    if (!is_valid_username($username)) {
        flash("Username must only contain 3-16 characters a-z, 0-9, _, or -", "danger");
        $hasError = true;
    }
    if (empty($password)) {
        flash("password must not be empty", "danger");
        $hasError = true;
    }
    if (empty($confirm)) {
        flash("Confirm password must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_password($password)) {
        flash("Password too short", "danger");
        $hasError = true;
    }
    if (
        strlen($password) > 0 && $password !== $confirm
    ) {
        flash("Passwords must match", "danger");
        $hasError = true;
    }
    if (!$hasError) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Users (email, password, username) VALUES(:email, :password, :username)");
        try {
            $stmt->execute([":email" => $email, ":password" => $hash, ":username" => $username]);
            flash("Successfully registered!", "success");


            $user_id = $db->lastInsertId();


            $role_stmt = $db->prepare("SELECT id FROM Roles WHERE name = 'client'");
            $role_stmt->execute();
            $role = $role_stmt->fetch(PDO::FETCH_ASSOC);

            if ($role) {
                $role_id = $role['id'];


                $user_role_stmt = $db->prepare("INSERT INTO UserRoles (user_id, role_id, is_active, created, modified) VALUES (:user_id, :role_id, 1, NOW(), NOW())");
                $user_role_stmt->execute([":user_id" => $user_id, ":role_id" => $role_id]);
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>
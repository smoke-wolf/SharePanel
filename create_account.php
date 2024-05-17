<?php
session_start();

// Function to check if a user exists
function userExists($username, $users) {
    foreach ($users as $token => $user) {
        if ($user['username'] === $username) {
            return true;
        }
    }
    return false;
}

// Function to create a unique token
function createUniqueToken() {
    return bin2hex(random_bytes(16));
}

// Function to create a user array
function createUserArray($username, $password, $developer_level = 0, $email = '') {
    $user = [
        "username" => $username,
        "password" => $password,
        "developer_level" => $developer_level,
        "allowed_dirs" => "Your_App"
    ];
    if ($email) {
        $user["email"] = $email;
    }
    return $user;
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check which form was submitted
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $users = json_decode(file_get_contents('Users/Users.json'), true);

        foreach ($users as $token => $user) {
            if ($user['username'] === $username && $user['password'] === $password) {
                $_SESSION['username'] = $username;
                header('Location: acc.php');
                exit;
            }
        }

        $error = 'Invalid username or password.';
    } elseif (isset($_POST['create'])) {
        $newUsername = $_POST['new_username'];
        $newPassword = $_POST['new_password'];
        $newEmail = $_POST['new_email'] ?? '';
        $users = json_decode(file_get_contents('Users/Users.json'), true);

        if (userExists($newUsername, $users)) {
            $error = 'Username already exists.';
        } else {
            $newToken = createUniqueToken();
            $newUser = createUserArray($newUsername, $newPassword, 0, $newEmail);
            $users[$newToken] = $newUser;
            file_put_contents('Users/Users.json', json_encode($users));
            $message = 'Account created successfully. Token: ' . json_encode([$newToken => $newUser]);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login or Create Account</title>
    <style>
        body {
            background-color: #222;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        h1, h2 {
            color: orange;
        }
        form {
            margin-top: 20px;
            margin-left: auto;
            margin-right: auto;
            width: 550px;
            padding: 20px;
            background-color: #333;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        label, input[type="text"], input[type="password"], input[type="submit"] {
            display: block;
            margin-bottom: 10px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 5px;
        }
        input[type="submit"] {
            background-color: orange;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .message {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Login or Create Account</h1>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    <?php if (isset($message)) { echo "<p class='message'>$message</p>"; } ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <input type="submit" name="login" value="Login">
    </form>
    <hr>
    <h2>Create an Account</h2>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <label for="new_username">Username:</label>
        <input type="text" id="new_username" name="new_username" required>
        <label for="new_password">Password:</label>
        <input type="password" id="new_password" name="new_password" required>
        <label for="new_email">Email (optional):</label>
        <input type="email" id="new_email" name="new_email">
        <input type="submit" name="create" value="Create Account">
    </form>
    <script>
        // Example JavaScript animation (meteor-like animation)
        const meteor = document.createElement('div');
        meteor.classList.add('meteor');
        document.body.appendChild(meteor);

        setTimeout(() => {
            meteor.style.opacity = 0;
        }, 500);

        setTimeout(() => {
            document.body.removeChild(meteor);
        }, 1000);
    </script>
</body>
</html>

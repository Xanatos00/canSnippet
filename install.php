<?php
/*
  @author: Ademcan (ademcan@ademcan.net)
  @name: install.php
  @description: installation page
  REMOVE AFTER SUCCESSFUL INSTALLATION
 */
include "config.php";

if (file_exists($config["dbname"])) {
    echo "Installation process already done";
} else {

?>
<head>
    <link rel="stylesheet" href="css/flat.css" type="text/css" media="screen" />
    <title>
        Installation of canSnippet
    </title>
</head>
<html>
    <body>
        <div id="installWindow">
            <b><font color="#27ae60">Welcome to the installation page of canSnippet <br>
            Please fill in the following form </font></b><br><br><br>
            <center>
            <form name="register" action="install.php" method="post">
                <table>
                <tr><td width="150px">Title :</td><td> <input type="text" name="title" /></td></tr>
                <tr><td>Username :</td><td> <input type="text" name="username" maxlength="30" /></td></tr>
                <tr><td>Email :</td><td> <input type="email" name="email" maxlength="30" /></td></tr>
                <tr><td>Password :</td><td> <input type="password" name="pass1" /></td></tr>
                <tr><td>Repeat Password :</td><td> <input type="password" name="pass2" /></td></tr>
                </table>
                </center>

                <?php
                if(is_writable(dirname($config["dbname"])))
                {
                ?>
                <input type="submit" value="Install" class="installButton"/>
                <?php
                }else{
                ?>
                <h2>You don't have write permissions on this directory <?= realpath(dirname($config["dbname"]))?></h2>
                <?php
                }
                ?>

            </form>

        </div>

        <?php
        // INSTALLATION
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // Create users table
            if (!class_exists('SQLite3'))
                die("SQLite 3 NOT supported.");

            $base = new SQLite3($config["dbname"]);

            $query = "CREATE TABLE user(
                    username VARCHAR(30) NOT NULL PRIMARY KEY UNIQUE,
                    status longtext,
                    password VARCHAR(64) NOT NULL,
                    salt VARCHAR(3) NOT NULL,
                    active integer,
                    email VARCHAR(30) NOT NULL UNIQUE
                )";
            $results = $base->exec($query);

            //retrieve our data from POST
            $username = $_POST['username'];
            $email = $_POST['email'];
            $pass1 = $_POST['pass1'];
            $pass2 = $_POST['pass2'];
            // check if both passwords are identical
            if ($pass1 != $pass2)
                header('Location: install.php');
            // limit the size of the username to 30 characters
            if (strlen($username) > 30)
                header('Location: install.php');
            $hash = hash('sha256', $pass1);

            //creates a 3 character sequence for salt
            function createSalt() {
                $string = md5(uniqid(rand(), true));
                return substr($string, 0, 3);
            }

            $salt = createSalt();
            $hash = hash('sha256', $salt . $hash);

            // special characters protection
            $title = htmlentities($_POST['title'],ENT_QUOTES);

            // Add the user to the database
            $status = "admin";
            $addUser = "INSERT INTO user(username, status, password, salt, active, email)
                VALUES ('$username', '$status' , '$hash' ,'$salt', 1, '$email')";
            $base->exec($addUser);

            // Create snippets table
            $createSnippetsDatabase = "CREATE TABLE snippets(
                ID INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                username longtext,
                language longtext,
                description longtext,
                name longtext,
                code longtext,
                date date,
				private integer,
				highlight longtext,
				lines integer,
                tags longtext,
                score real,
                rate_counter integer
                )";
            $base->exec($createSnippetsDatabase);

            // create scoring trace database
            $createScoringDatabase = "CREATE TABLE scoring(
                ID INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                username longtext,
                snippet integer
                )";
            $base->exec($createScoringDatabase);

            // Create settings table
            $createSettingsDatabase = "CREATE TABLE settings(
                ID INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                username longtext,
                title longtext,
                theme longtext,
                prismtheme longtext,
                email longtext,
                lng longtext
                )";
            $base->exec($createSettingsDatabase);

            // Create tags table
            $createTagsDatabase = "CREATE TABLE tags(
                ID INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                tag longtext,
                count integer
                )";
            $base->exec($createTagsDatabase);


            // Create Pwd reset (token) table
            $createPwdtokenDatabase = "CREATE TABLE pwdtoken(
                ID INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                email longtext,
                token longtext,
                timestamp date
                )";
            $base->exec($createPwdtokenDatabase);



            // Add default settings to database
            $addDefaultSettings = "INSERT INTO settings(username, title, theme, prismtheme, email, lng)
                VALUES ('$username' , '$title', 'flat', '".$config["defaultPrismCSS"]."', '$email', 'en')";
            $base->exec($addDefaultSettings);

            ?>
            <script>
                location.href = 'index.php';
            </script>
            <?php
        }
        ?>
    </body>
</html>

<?php } ?>

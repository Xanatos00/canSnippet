

<?php
/*
  @author: Ademcan (ademcan@ademcan.net)
  @name: thankyou.php
  @description: Account creation successfull
 */

 require_once("./config.php");
 $lng = language();

 switch ($lng) {
     case "en":
         require("./en.php");
         break;
     case "fr":
         require("./fr.php");
         break;
 }
?>

<html>
<head>
    <link rel="stylesheet" href="css/flat.css" type="text/css" media="screen" />
</head>
<body>
    <center>
        <img src="images/canSnippetLogo_CE_200x200_new.png" style="width:200px;padding-top:50px;"/><br />
    <div style="">
        <br /><br />
        <?php echo($messages['accountcreationreceived']); ?>
        <br />
        <?php echo($messages['accountcreationreceived2']); ?>
        <br /><br />
        <button type="button" class="homeButton" onclick='document.location.href="index.php";'><?php echo($messages['home']); ?></button>
    </div>

    </center>


</body>

</html>

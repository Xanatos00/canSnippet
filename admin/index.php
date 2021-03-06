<?php
/*
  @author: Ademcan (ademcan@ademcan.net)
  @name: index.php
  @description: index page for the admin panel
 */

 // ini_set('display_errors', 'On');
 // error_reporting(E_ALL);

session_start();
// date_default_timezone_set("UTC");

if (!isset($_SESSION['valid']) || !$_SESSION['valid']) {
    header("location:login.php");
}

else {
    // CONNECTION TO THE DATABASE
    include 'admin-menu.php';
    $dbname = '../snippets.sqlite';
    $mytable = "snippets";
    $base = new SQLite3($dbname);

    if(isset($_GET['view']) ){
        $view = $_GET['view'];
    } else {
        $view = "all";
    }


    if ($view == "all"){
        $count_query = "SELECT count(*) as count FROM $mytable WHERE username=\"".$username."\"";
        $results_count = $base->query($count_query);
    }
    elseif ($view == "public"){
        $count_query = "SELECT count(*) as count FROM $mytable WHERE username=\"".$username."\" AND private='off'";
        $results_count = $base->query($count_query);
    }
    else {
        $count_query = "SELECT count(*) as count FROM $mytable WHERE username=\"".$username."\" AND private='on' ";
        $results_count = $base->query($count_query);
    }

    // $count_query = "SELECT count(*) as count FROM $mytable WHERE username=\"".$username."\"";
    // $results_count = $base->query($count_query);
    $row_count = $results_count->fetchArray();
    $snippets_count = $row_count['count'];

    $limit = 10;
    $page = 1;

    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    }
    $start_count = $limit * ($page - 1);

    $username = $_SESSION['username'];

    $queryU = "SELECT * FROM user WHERE username=\"".$username."\" ";
    $resultsU = $base->query($queryU);
    $rowU = $resultsU->fetchArray();
    $status = $rowU["status"];

    if ($view == "all"){
        $query_name = "SELECT * FROM $mytable WHERE username=\"".$username."\" ORDER BY date DESC LIMIT $start_count,$limit";
    }
    elseif ($view == "public"){
        $query_name = "SELECT * FROM $mytable WHERE username=\"".$username."\" AND private='off' ORDER BY date DESC LIMIT $start_count,$limit";
    }
    else {
        $query_name = "SELECT * FROM $mytable WHERE username=\"".$username."\" AND private='on' ORDER BY date DESC LIMIT $start_count,$limit";
    }

    $title = $messages['mysnippets']." (".$snippets_count.")";

    $results_name = $base->query($query_name);

    echo '<div id="validationMessage" style="padding-left:10px;padding-top:5px;position:fixed; width:100%; height:30px;background-color:orange;display:none;">Merci. Votre nouveau snippet sera validé sous peu.</div>';

    if( isset($_SESSION['newSnippet'])){
        if ($_SESSION['newSnippet'] == 1){
            ?>
                <script>
                    document.getElementById("validationMessage").style.display = 'block';
                </script>
            <?php
            $_SESSION['newSnippet'] = 0;
        }
    }

    echo '<h1>'.$title.'</h1>';
    echo '<div><b>'.$messages['show'].'</b>  <a href="index.php?view=all" class="editButton" style="margin-right:10px !important; ">'.$messages['all'].'</a>';
    echo '<a href="index.php?view=public" class="greenButton" style="margin-right:10px !important; ">'.$messages['published'].'</a>';
    echo '<a href="index.php?view=private" class="deleteButton">'.$messages['unpublished'].'</a></div><div id="newSnippet">';

    if ($snippets_count == 0){
        echo "".$messages['nosnippetyet']."";
    }

    // Loop and write all the recent snippets
    while($row = $results_name->fetchArray())
    {
        $username = $row['username'];
        $name = $row['name'];
        $code = $row['code'];
        $language = $row['language'];
        $private = $row['private'];
        $lines = $row['lines'];
        $highlight = $row['highlight'];
        $date = $row['date'];
        $description = $row['description'];
        $tags = $row['tags'];

        $id = $row['ID'];

        if ($status == "admin"){
            if ($private=="on"){
                echo '<div style="background-color:#BDEDFF;margin-top:20px;"><h2><font color="#27ae60">'.$name.' ['.$language.']</font><a href="action.php?action=activate&id=' . $row['ID'] . '" onclick="return confirm(\'',$messages['reallyactivatesnippet'],'\');"><img src="../images/lockFlat.png" style="width:20px; height: 20px;padding-left:10px;" /></a></h2>';
            }
            else {
                echo '<div style="margin-top:20px;"><h2><font color="#27ae60">'.$name.' ['.$language.']</font><a href="action.php?action=deactivate&id=' . $row['ID'] . '" onclick="return confirm(\'',$messages['reallydeactivatesnippet'],'\');"><img src="../images/lockFlat_open.png" style="width:20px; height: 20px;padding-left:10px;" /></a></h2>';
                // echo '<div style="margin-top:20px;"><h2><font color="#27ae60">'.$name.' ['.$language.']</font></h2>';
            }
        }
        else {
            if ($private=="on"){
                echo '<div style="background-color:#BDEDFF;margin-top:20px;"><h2><font color="#27ae60">'.$name.' ['.$language.']</font><img src="../images/lockFlat.png" style="width:20px; height: 20px;padding-left:10px;" /></h2>';
            }
            else {
                echo '<div style="margin-top:20px;"><h2><font color="#27ae60">'.$name.' ['.$language.']</font><img src="../images/lockFlat_open.png" style="width:20px; height: 20px;padding-left:10px;" /></h2>';
                // echo '<div style="margin-top:20px;"><h2><font color="#27ae60">'.$name.' ['.$language.']</font></h2>';
            }
        }


        if ($language=="html"){
            $languageClass = "language-markup";
        }
        else if ($language=="text"){
            $languageClass = "language-markup";
        }
        else{
            $languageClass = "language-".$language;
        }

        // If admin is connected, show the snippet's creator
        if ($status == "admin"){
            echo '<img src="../images/author.png" style="width:13px; height:13px;padding-left:10px;padding-right:10px;"/><font size="4"><b>'.$username.'</b></font><br>';
        }

        $date=date_create($date);
        $formattedDate = date_format($date,"d.m.Y");
        echo '<img src="../images/calendar.png" style="width:13px; height:13px;padding-left:10px;padding-right:10px;"/>'.$formattedDate.'';
        echo '<br><img src="../images/comment.png" style="width:13px; vertical-align: middle; height:13px;padding-left:10px;padding-right:10px;"/>';
        echo ''.nl2br($description);

        echo '<br /><div style="word-wrap: break-word;"><img src="../images/tag.png" style="width:13px; height:13px;padding-left:10px;padding-right:10px;"/>';
        $tagsList=explode(",",$tags);
        foreach($tagsList as $var){
            // $lowtag = strtolower($var);
            // $lowtag = str_replace(' ', '', $var);

            $lowtag=$var;
            echo '<u>'.$lowtag.'</u>&nbsp;';
        }
        echo '</div>';
        echo '<section class="'.$languageClass.'"> <pre class="line-numbers"><code>'.$code.'</code></pre> </section>';
        echo '<a href="action.php?action=edit&id=' . $row['ID'] . '" class="editButton">'.$messages['edit'].'</a>';
        echo '<a href="action.php?action=delete&id=' . $row['ID'] . '" onclick="return confirm(\'',$messages['reallydeletesnippet'],'\');" class="deleteButton">'.$messages['delete'].'</a>';
        echo '<hr><br></div>';
    }

    echo "<br><br>";

    // Pagination
    // First page
    if ($snippets_count > $limit & $page == 1) {
        echo '<center><a href= "index.php?view='.$view.'&page=2"> '.$messages['oldersnippets'].' >>> </a></center>';
    }
    // Last page
    if ($page > 1 & $snippets_count <= ($limit * $page) & $snippets_count > ($limit * ($page - 1))) {
        echo '<center><a href= "index.php?view='.$view.'&page=' . ($page - 1) . '"> <<< '.$messages['newestsnippets'].' </a></center>';
    }
    // Middle page
    if ($page > 1 & $snippets_count > ($limit * $page)) {
        echo '<center><a href= "index.php?view='.$view.'&page=' . ($page - 1) . '"> <<< '.$messages['newestsnippets'].'</a> -- <a href="index.php?page=' . ($page + 1) . '">Older snippets >>></a></center>';
    }
    ?>

    </center><br><br></div></body></html>

    <?php
}
?>

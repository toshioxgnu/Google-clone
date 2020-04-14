<!doctype html>
<html lang="en">
<head>
    <?php
        include("config.php");
        include("classes/SitesResultsProvider.php");
        $term = isset($_GET['term']) ? $_GET['term'] : "hallo";
        $type = isset($_GET['type']) ?  $_GET['type'] : "sites";
     ?>
    <link rel="icon" href="assets/images/google-clone-logo.png" type="image/png">
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Search <?php echo $term?></title>
    <link rel="stylesheet" href="assets/css/all.css">
    <script src="https://kit.fontawesome.com/8ef846cb6c.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <div class="headerContent">
                <div class="logoContainer">
                    <a href="index.php"><img alt="" src="assets/images/google-clone-logo.png"></a>
                </div>
                
                <div class="searchContainer">
                    <form action="search.php" method="GET">
                        <div class="searchBarContainer">
                            <input type="text" name="term" class="searchBar" value="<?php echo $term?>">
                            <button class="search"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="tabsContainer">
                <ul class="tabList">
                    <li class="<?php echo $type == 'sites' ? 'active' : '' ; ?>"><a href='<?php echo "search.php?term=$term&type=sites"; ?>' >Sites</a></li>
                    <li class="<?php echo $type == 'images' ? 'active' : '' ; ?>"><a href='<?php echo "search.php?term=$term&type=images"; ?>' >Images</a></li>
                </ul>
            </div>

        </div>

        <div class="mainresultsection">
            <?php
                $resultsProvider = new SitesResultsProvider($con);

                $numResults =  $resultsProvider -> getnumResults($term);

                echo "<p class='resultCount'> $numResults  Results Found</p>";

                echo $resultsProvider -> getResultsHtml(1,20,$term);
            ?>
        </div>


    </div>


</body>
</html>

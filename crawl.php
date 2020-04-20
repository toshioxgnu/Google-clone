<?php
include("config.php");
include("classes/DomDocumentparser.php");

$alreadyCrawled = array();
$crawling = array();
$alreadyFoundImages = array();

$startUrl = $_GET["term"];
followLinks($startUrl);

function InsertLink($url, $title, $description, $keywords, $author){
    global $con;

    $query= $con->prepare("INSERT INTO sites(url,title,description,keywords ,author)
                                    VALUES(:url, :title, :description, :keywords, :author)");
    $query ->bindParam(":url", $url);
    $query ->bindParam(":title", $title);
    $query ->bindParam(":description", $description);
    $query ->bindParam(":keywords", $keywords);
    $query ->bindParam(":author", $author);

    return $query -> execute();
}

function InsertImages($url, $src, $title, $alt){
    global $con;

    $query= $con->prepare("INSERT INTO images(siteUrl, imageUrl ,title,alt )
                                    VALUES(:siteUrl, :imageUrl, :title, :alt)");
    $query ->bindParam(":siteUrl", $url);
    $query ->bindParam(":imageUrl", $src);
    $query ->bindParam(":title", $title);
    $query ->bindParam(":alt", $alt);

    return $query -> execute();
}

function linkExist($url){
    global $con;

    $query= $con->prepare("select * from sites where url = :url");
    $query ->bindParam(":url", $url);
    $query -> execute();
    return  $query -> rowCount() != 0 ;
}

function createLink($src, $url){
    $scheme = parse_url($url)["scheme"];
    $host = parse_url($url)["host"];
    if(substr($src, 0,2) == "//"){
        $src = parse_url($url)["scheme"] . ":". $src;
    }else if(substr($src, 0,1) == "/"){
        $src = $scheme . "://" . $host . $src ;
    }else if(substr($src, 0,2) == "./"){
        $src = $scheme . "://" . $host . dirname(parse_url($url)["path"]) . substr($src, 1);
    }else if(substr($src, 0,3) == "../"){
        $src = $scheme . "://" . $host . "/". $src;
    }else if(substr($src, 0,5) !== "https" && substr($src, 0,5) !== "http"){
        $src = $scheme . "://" . $host . "/". $src;
    }

    return $src;
}

function getDetails($url){
    $parser = new DomDocumentparser($url);
    $titleArray = $parser ->gettitle();
    global $alreadyFoundImages;

    if(sizeof($titleArray) == 0 || $titleArray -> item(0) == NULL){
        return;
    }
    $title = $titleArray -> item(0)->nodeValue;
    $title = str_replace("\n","",$title);
    if($title == ""){
        return;
    }

    $description = "";
    $keywords = "";
    $author = "";
    $metasArray = $parser -> getMetaTags();

    foreach ($metasArray as $meta){
        if($meta -> getAttribute("name") == "description"){
            $description = $meta -> getAttribute("content");
        }
        if($meta -> getAttribute("name") == "keywords"){
            $keywords = $meta -> getAttribute("content");
        }
        if($meta -> getAttribute("name") == "author"){
            $author = $meta -> getAttribute("content");
        }
    }

    $description = str_replace("\n","",$description);
    $keywords = str_replace("\n","",$keywords);
    $author = str_replace("\n","",$author);

    if(linkExist($url)){
        echo "$url already exist";
    }else if( insertLink($url, $title, $description, $keywords, $author)){
        echo "success";
    }else{
        echo "ERROR: Failed to insert $url";
    }

    $imageArray = $parser -> getImages();

    foreach ($imageArray as $image){
        $src = $image -> getAttribute("src");
        $alt = $image -> getAttribute("alt");
        $title = $image -> getAttribute("title");

        if(!$title && !$alt){
            continue;
        }

        $src = createLink($src, $url);

        if(!in_array($src, $alreadyFoundImages)){
            $alreadyFoundImages[] = $src;
            InsertImages($url, $src, $title, $alt);
        }
    }

}

function followLinks($url){
    global $alreadyCrawled;
    global $crawling;

    $parser = new DomDocumentparser($url);
    $linkList = $parser ->getLinks();

    foreach ($linkList as $link){
        $href = $link -> getAttribute("href");
        if(strpos($href, "#") !== false){
            continue;
        }else if(substr($href, 0,11) == "javascript:"){
            continue;
        }
        $href = createLink($href, $url);

        if(!in_array( $href , $alreadyCrawled)){
            $alreadyCrawled[] = $href;
            $crawling[] = $href;

            getDetails($href);
        }

    }

    array_shift($crawling);

    foreach ($crawling as $site){
        followLinks($site);
    }

}
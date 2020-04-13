<?php
include("config.php");
include("classes/DomDocumentparser.php");

$alreadyCrawled = array();
$crawling = array();

$startUrl = "http://www.utem.cl";
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

    insertLink($url, $title, $description, $keywords, $author);
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
        }else return;

    }

    array_shift($crawling);

    foreach ($crawling as $site){
        followLinks($site);
    }

}
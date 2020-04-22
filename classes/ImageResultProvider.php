<?php


class ImageResultProvider
{
    private $con;

    public function __construct($con)
    {
        $this -> con = $con;
    }

    public function getnumResults($term){
        $query = $this -> con  -> prepare("select count(*) as total 
        from images where title like :term or alt like :term and broken = 0");
        $searchterm = "%".$term."%";
        $query -> bindparam(":term", $searchterm );
        $query -> execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row["total"];
    }

    public function getResultsHtml($page, $pageSize, $term){
        $fromLimit = ($page -1) * $pageSize;

        $query = $this -> con  -> prepare("select *
        from images where title like :term or alt like :term order by clicks desc LIMIT :fromLimit , :pageSize");

        $searchterm = "%".$term."%";
        $query -> bindparam(":term", $searchterm );
        $query -> bindparam(":fromLimit", $fromLimit, PDO::PARAM_INT);
        $query -> bindparam(":pageSize", $pageSize ,PDO::PARAM_INT);
        $query -> execute();

        $resultsHtml = "<div class='imageResults'>";
        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $id = $row["id"];
            $imageUrl = $row["imageUrl"];
            $siteUrl = $row["siteUrl"];
            $title = $row["title"];
            $alt = $row["alt"];

            if($title){
                $displaytext = $title;
            }else if($alt){
                $displaytext = $alt;
            }else{
                $displaytext = $imageUrl;
            }

            $resultsHtml .= "<div class='gridItem'> 

                            <a href='$imageUrl'>
                                <img src='$imageUrl' >
                               </a> 
                        </div>";
        }
        $resultsHtml .= "</div> ";

        return $resultsHtml;
    }

}
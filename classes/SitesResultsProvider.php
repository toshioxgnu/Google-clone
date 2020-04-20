 <?php


class SitesResultsProvider
{
    private $con;

    public function __construct($con)
    {
        $this -> con = $con;
    }

    public function getnumResults($term){
        $query = $this -> con  -> prepare("select count(*) as total 
        from sites where title like :term or url like :term or keywords like :term 
        or description like :term ");
        $searchterm = "%".$term."%";
        $query -> bindparam(":term", $searchterm );
        $query -> execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row["total"];
    }

    public function getResultsHtml($page, $pageSize, $term){
        $fromLimit = ($page -1) * $pageSize;

        $query = $this -> con  -> prepare("select  *
        from sites where title like :term or url like :term or keywords like :term 
        or description like :term order by clicks desc LIMIT :fromLimit , :pageSize");

        $searchterm = "%".$term."%";
        $query -> bindparam(":term", $searchterm );
        $query -> bindparam(":fromLimit", $fromLimit, PDO::PARAM_INT);
        $query -> bindparam(":pageSize", $pageSize ,PDO::PARAM_INT);
        $query -> execute();

        $resultsHtml = "<div class='sitesResults'>";
        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $id = $row["id"];
            $url = $row["url"];
            $title = $row["title"];
            $description = $row["description"];

            $title = $this->trimField($title, 55);
            $description = $this->trimField($description, 230);
            $resultsHtml .= "<div class='resultContainer'> 

                            <h3 class='title'><a class='result' href='$url'>$title</a></h3>
                            <span class='url'>$url</span>    
                            <span class='description'>$description</span>    
                        </div>";
        }
        $resultsHtml .= "</div> ";

        return $resultsHtml;
    }

    private function trimField($string, $characterLimit){
        $dots = strlen($string) > $characterLimit ? "..." : "";
        return substr($string, 0, $characterLimit). $dots;
    }
}
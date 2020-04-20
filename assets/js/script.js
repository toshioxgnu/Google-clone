$(document).ready(function(){
    $(".result").on("click", function(){
        var url = $(this).attr("href");
        var id = $(this).attr("data-LinkId");
        if(!id){
            alert("data-LinnkId not found");
        }

        increaseLinksClicks(id, url);
        return false;
    })
})

function increaseLinksClicks(linkId, url){
    $.post("ajax/updateLinkCount.php", {LinkId: linkId})
        .done(function(result){
            console.log(result);
            return;
        });
}
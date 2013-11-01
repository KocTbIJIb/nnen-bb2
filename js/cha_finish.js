$(document).ready(function(){

    var url = "http://nnen.ru/championship/finish";
    var data = {};

    var team_hash = $(".team_hash").eq(0);
    if (!team_hash.length || team_hash.val().length < 32) {
        alert('Unauthorized');
        return;
    }

    data.team_hash = team_hash.val();

    postCORS(url, data, function(data){return false;});
});
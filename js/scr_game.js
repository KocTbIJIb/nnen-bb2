$(document).ready(function(){
    var url = "http://nnen.ru/score/";
    var data = {};
    
    var team_hash = $(".team_hash").eq(0);
    if (!team_hash.length || team_hash.val().length < 32) {
        alert('Unauthorized');
        return;
    }

    $('#scr-check').click(function(){
        postCORS(url + 'check', {team_hash: team_hash.val()}, checkCallback);
        return false;
    });

    data.team_hash = team_hash.val();
    data.sectors = collectSectors();

    postCORS(url + 'game', data, responseProcessing);
});

function collectSectors() {
    var codes = [];
    $('.cols-wrapper .cols .color_correct').each(function(i){
        codes.push($(this).html());
    });
    return codes;
}

function responseProcessing(data) {
    if (typeof(data) == "string") {
        data = eval("(" + data + ")");
    }
    $('#game-team-name').append('<h1>Команда: ' + data.team + '</h1>');
    if (data.status == 'win') {
        $('#scr-score').html('Проходной код: <span style="color:#FFC0CB">' + data.code + '</span>');
    }
    return false;
}

function checkCallback(data) {
    if (data.status == 'score') {
        $('#scr-score').html('Ваш счёт: <span style="font-weight:bold;font-size:66px;color:#FFC0CB">' + data.score + '</span>');
        return;
    }

    if (typeof(data.seconds) != 'undefined') {
        $('#scr-score').html('Вы сможете узнать ваш счёт через ' + data.seconds + ' секунд');
    }
    return;
}
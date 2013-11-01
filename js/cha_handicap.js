champSecondsLeft = [];
champRenderInit = null;
champTimeOutId = null;
$(document).ready(function(){


    var url = "http://nnen.ru/championship/handicap";
    var data = {};

    var team_hash = $(".team_hash").eq(0);
    if (!team_hash.length || team_hash.val().length < 32) {
        alert('Unauthorized');
        return;
    }

    data.team_hash = team_hash.val();

    postCORS(url, data, responseProcessing);
});

function responseProcessing(data) {
    if (typeof(data) == "string") {
        data = eval("(" + data + ")");
    }

    if (data.status == 'win') {
        $('#champ-result').html('<h1>Проходной код: ' + data.code + '</h1>');
        return;
    } 

    champSecondsLeft = data.secondsLeft;
    initRenderCounter();
    return;
}

function initRenderCounter() {
    if (champTimeOutId) {
        clearInterval(champTimeOutId);
    }
    champRenderInit = new Date();
    champTimeOutId = setInterval(function() {
        renderCounter();
    }, 1000);

}

function renderCounter() {
    var now = new Date;
    var diff = parseInt((now.getTime() - champRenderInit.getTime()) / 1000);

    var secs = champSecondsLeft - diff;
    if (secs <= 0) {
        clearInterval(champTimeOutId);
        location.reload();
    }

    $('#champ-result').html('<h1>Вы получите проходной код через ' + secs + ' секунд</h1>');
}
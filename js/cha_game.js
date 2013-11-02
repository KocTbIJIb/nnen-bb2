champLabels = [];
champrenderInit = null;
champTimeOutId = null;
$(document).ready(function(){


    var url = "http://nnen.ru/championship";
    var data = {};

    var team_hash = $(".team_hash").eq(0);
    if (!team_hash.length || team_hash.val().length < 32) {
        alert('Unauthorized');
        return;
    }

    $('#champ-take-label').click(function(){
        if (champLabels.length >= 3) {
            $('#champ-alert').html('<h2>Вы уже взяли максимальное количество одновременных меток</h2>');
            return false;
        }
        postCORS(url + '/pick', {team_hash: team_hash.val()}, pickCallback);
        return false;
    });

    data.team_hash = team_hash.val();
    data.sectors = collectSectors();

    postCORS(url + '/game', data, responseProcessing);
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

    $('#champ-game-field').append('<p>Текущие метки: </p><ul></ul>');
    champLabels = data.current;
    initRenderLabels();

    $('#champ-content').append('<table></table>');
    $('#champ-content table').append('<tr><td><b>#</b></td><td><b>Команда</b></td><td><b>Результат</b></td></tr>');
    for (var i in data.table) {
        var totalString = new Date(data.table[i].total * 1000);
        var html = '<tr><td>' + (parseInt(i)+1) + '</td>';
            html+= '<td>' + data.table[i].name + '</td>';
            html+= '<td style="text-align: right">' + totalString.getHours() + ':';
        var minutes = totalString.getMinutes() < 10 ? '0' + totalString.getMinutes() : totalString.getMinutes();
            html+= minutes + ':';
        var seconds = totalString.getSeconds() < 10 ? '0' + totalString.getSeconds() : totalString.getSeconds();
            html+= seconds + '</td></tr>';
        $('#champ-content table').append(html);
        
    }
    return;
}

function pickCallback(data) {
    if (typeof(data) == "string") {
        data = eval("(" + data + ")");
    }

    if (data.status == 'ok') {
        champLabels = data.current;
        initRenderLabels();        
    } else {
        $('#champ-alert').html('<h2>' + data.message + '</h2>');
    }

}

function initRenderLabels() {
    if (champTimeOutId) {
        clearInterval(champTimeOutId);
    }
    champrenderInit = new Date();
    champTimeOutId = setInterval(function() {
        renderLabels();
    }, 1000);

}

function renderLabels() {
    var now = new Date;
    var diff = parseInt((now.getTime() - champrenderInit.getTime()) / 1000);
    $('#champ-game-field ul').html('');
    if (champLabels.length == 0) {
        $('#champ-game-field ul').append('<li>Нет текущих меток</li>');
    } else {
        for(var i in champLabels) {
            var secs = champLabels[i].secondsLeft - diff;
            if (secs <= 0) {
                clearInterval(champTimeOutId);
                location.reload();
            }
            $('#champ-game-field ul').append('<li>Метка №' + champLabels[i].label_id + ' - Осталось ' + secs + ' секунд</li>');
        }
    }
}
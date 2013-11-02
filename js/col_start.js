$(document).ready(function(){
    var url = "http://nnen.ru/colonization/start";
    var data = {};
    
    var team_hash = $(".team_hash").eq(0);
    if (!team_hash.length || team_hash.val().length < 32) {
        alert('Unauthorized');
        return;
    }
    data.team_hash = team_hash.val();
    data.sectors = collectSectors();

    postCORS(url, data, responseProcessing);
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
    switch (data.status) {
        case 'win':
            $('#win-code').html('Проходной код: ' + data.code);
            break;
        case 'error':
            alert(data.error);
            break;
        case 'game':
        default:
            $('#game-team-name').append('<h1>Команда: ' + data.team + '</h1>');

            $('#game-info').append('<p>Построеные объекты:</p>');
            $('#game-info').append('<div id="game-roads"><p>Дороги:</p><ul></ul></div>');
            $('#game-info').append('<div id="game-towns"><p>Города:</p><ul></ul></div>');
            $.each(data.objects, function(index, value) {
                console.log(value);
                if (value.type == 'road') {
                    $('#game-roads ul').append('<li>' + value.name +'</li>');
                } else {
                    $('#game-towns ul').append('<li>' + value.name + (parseInt(value.count) == 1 ? ' (поселение)' : ' (город)') + '</li>');
                }
            })
            $('#game-info').append('<div style="clear:both"></div>');
            
            if (data.message.length > 0) {
                $('#win-code').html(data.message.join('; '));
            }
            break;
    }
}

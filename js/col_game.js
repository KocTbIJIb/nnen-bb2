colBalance = {wood:0,stone:0,flax:0,water:0};
$(document).ready(function(){
    var url = "http://nnen.ru/colonization/game";
    var data = {};
    
    var team_hash = $(".team_hash").eq(0);
    if (!team_hash.length || team_hash.val().length < 32) {
        alert('Unauthorized');
        return;
    }

    data.team_hash = team_hash.val();
    data.sectors = collectSectors();
    data.resources = collectBonuses();

    postCORS(url, data, responseProcessing);
});

function collectSectors() {
    var codes = [];
    $('.cols-wrapper .cols .color_correct').each(function(i){
        codes.push($(this).html());
    });
    return codes;
}

function collectBonuses() {
    var codes = [];
    $('h3.color_correct').each(function(i){
        codes.push($(this).next().html());
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
                if (value.type == 'road') {
                    $('#game-roads ul').append('<li>' + value.name +'</li>');
                } else {
                    $('#game-towns ul').append('<li>' + value.name + (parseInt(value.count) == 1 ? ' (поселение)' : ' (город)') + '</li>');
                }
            })
            $('#game-info').append('<div style="clear:both"></div>');

            colBalance = data.balance;
            $('#game-balance').append('<p>Игровой баланс:</p><ul></ul>');
            $('#game-balance ul').append('<li>Дерево: ' + (data.balance ? parseInt(data.balance.wood) : 0) + '</li>');
            $('#game-balance ul').append('<li>Камень: ' + (data.balance ? parseInt(data.balance.stone) : 0) + '</li>');
            $('#game-balance ul').append('<li>Лён: ' + (data.balance ? parseInt(data.balance.flax) : 0) + '</li>');
            $('#game-balance ul').append('<li>Вода: ' + (data.balance ? parseInt(data.balance.water) : 0) + '</li>');
            $('#game-balance ul').append('<li>Очки: ' + (data.balance ? parseInt(data.balance.total) : 0) + '</li>');
            $('#game-balance').append('<div style="clear:both"></div>');

            $('#game-exchange').append('<p>Обмен ресурсов <span>(Обменный курс - 4 к 1)</span></p>');

            $('#game-exchange').append('<select id="game-exchange-num"></select>');
            $('#game-exchange-num').append('<option value="">Сколько поменять?</select>');
            $('#game-exchange-num').append('<option value="4">4</select>');
            $('#game-exchange-num').append('<option value="8">8</select>');
            $('#game-exchange-num').append('<option value="12">12</select>');
            $('#game-exchange-num').append('<option value="16">16</select>');

            $('#game-exchange').append('<select id="game-exchange-from"></select>');
            $('#game-exchange-from').append('<option value="">Что поменять?</select>');
            $('#game-exchange-from').append('<option value="wood">Дерево</select>');
            $('#game-exchange-from').append('<option value="stone">Камень</select>');
            $('#game-exchange-from').append('<option value="flax">Лён</select>');
            $('#game-exchange-from').append('<option value="water">Вода</select>');

            $('#game-exchange').append('<select id="game-exchange-to"></select>');
            $('#game-exchange-to').append('<option value="">На что поменять?</select>');
            $('#game-exchange-to').append('<option value="wood">Дерево</select>');
            $('#game-exchange-to').append('<option value="stone">Камень</select>');
            $('#game-exchange-to').append('<option value="flax">Лён</select>');
            $('#game-exchange-to').append('<option value="water">Вода</select>');

            $('#game-exchange').append('<a href="#" id="game-exchange-do">Поменять</a>');


            if (data.message.length > 0) {
                $('#win-code').html(data.message.join('; '));
            }

            $('#game-exchange-do').click(function(){
                var num = parseInt($('#game-exchange-num option:selected').val());
                if (!num) {
                    alert('Не указана сумма для обмена');
                    return false;
                }

                var from = $('#game-exchange-from option:selected').val();
                if (from == '') {
                    alert('Не указан тип ресурса для обмена');
                    return false;
                }
                if (colBalance[from] < num) {
                    alert('Извините, но у вас столько нет');
                    return false;
                }

                var to = $('#game-exchange-to option:selected').val();
                if (to == '') {
                    alert('Не указан необходимый тип ресурса');
                    return false;
                }

                if (to == from) {
                    alert('Укажите разные типы ресурсов');
                    return false;
                }

                postCORS('http://nnen.ru/colonization/exchange', {
                    num: num,
                    from: from,
                    to: to,
                    team_hash: $(".team_hash").eq(0).val()
                }, exchangeCallback);
                return false;
            });
            break;
    }
}

function exchangeCallback(data) {
    if (data.status == 'error') {
        alert(data.error);
        return false;
    }
    location.reload();
}
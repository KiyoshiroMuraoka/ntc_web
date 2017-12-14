$(function () {
    var page = 0;

    $('#time-table-add-form').submit(function (event) {
        event.preventDefault();
        var name = $('#time-table-add-form [name=time_table_name]').val();
        var $checked = $('#time-table-add-form [name=member]:checked');
        var member = $checked.map(function (index, el) {
            return $(this).val();
        });
        var use_template = $('#time-table-add-form [name=use_template]').val().replace(" selected", "");

        if (name == "") {
            alert("タイムテーブル名を入力してください。");
            exit;
        }
        if (use_template == "") {
            alert("使用するテンプレートを選択してください。");
            exit;
        }
        if (member.get() == "") {
            alert("メール送信対象の顧客を選択してください。");
            exit;
        }
        TimeTableAdd(name, use_template, member.get());
    });

    $('#time-table-delete-form').submit(function (event) {
        event.preventDefault();
        var $checked = $('#time-table-delete-form [name=id]:checked');
        var time_table = $checked.map(function (index, el) {
            return $(this).val();
        });
        if (time_table.get() == "") {
            alert("削除するタイムテーブルを選択してください。");
            exit;
        }
        if (!confirm("選択されたタイムテーブルを削除します。\nこの動作は取り消しできません。")) {
            return false;
        }
        TimeTableDelete(time_table.get());
    });

    function TimeTableAdd(name, use_template, member) {
        var url = "./time-table-add.php";
        var now = new Date();
        var y = now.getFullYear();
        var m = now.getMonth() + 1;
        var d = now.getDate();
        var data = {
            'name': name,
            'use_template': use_template,
            'delivery_member': member,
            'delivery_schedule': 0,
            'register_date': y + "-" + m + "-" + d
        };
        var $form = $('<form/>', {
            'action': url,
            'method': 'post'
        });
        for (var key in data) {
            $form.append($('<input/>', {
                'type': 'hidden',
                'name': key,
                'value': data[key]
            }));
        }
        $form.appendTo(document.body);
        $form.submit();
    }

    function TimeTableDelete(id) {
        var url = "./time-table-delete.php";
        var data = {
            'id': id
        };
        var $form = $('<form/>', {
            'action': url,
            'method': 'post'
        });
        for (var key in data) {
            $form.append($('<input/>', {
                'type': 'hidden',
                'name': key,
                'value': data[key]
            }));
        }
        $form.appendTo(document.body);
        $form.submit();
    }

    function draw() {
        $('#page').html(page + 1);
        $('tr').hide();
        $('tr:first,tr:gt(' + page * 10 + '):lt(10)').show();
        if (page > 0) {
            $('#prev').css('display', 'inline');
        } else {
            $('#prev').css('display', 'none');
        }
        if (page < ($('tr').size() - 1) / 10 - 1) {
            $('#next').css('display', 'inline');
        } else {
            $('#next').css('display', 'none');
        }
    }
    $('#prev').click(function () {
        if (page > 0) {
            page--;
            draw();
        }
    });
    $('#next').click(function () {
        if (page < ($('tr').size() - 1) / 10 - 1) {
            page++;
            draw();
        }
    });
    draw();
    setTimeout(function () {
        if ($("#success").css("display") !== "none") {
            $("#success").slideUp();
        }
        if ($("#err").css("display") !== "none") {
            $("#err").slideUp();
        }
    }, 3000);
});

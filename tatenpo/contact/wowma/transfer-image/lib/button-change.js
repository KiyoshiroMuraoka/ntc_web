﻿$(function () {
    var agree = $('#agree');
    if(agree !== undefined){
        if(agree.is(":checked")) {
            $('#submit').removeAttr('disabled').css("color","#333");
        } else {
            $('#submit').attr('disabled', 'disabled').css("color","#efefef");
        }
    }

    $('#agree').click(function() {
        if($(this).is(":checked")) {
            $('#submit').removeAttr('disabled').css("color","#333");
        } else {
            $('#submit').attr('disabled', 'disabled').css("color","#efefef");
        }
    });

    $('#js-upload').on('change', function() {
        console.log("a");
        //選択したファイル情報を取得し変数に格納
        var file = $(this).prop('files')[0];
        //アイコンを選択中に変更
        $('#js-selectFile').find('#js-upload-icon').addClass('select').html('選択中');
        //未選択→選択の場合（.filenameが存在しない場合）はファイル名表示用の<div>タグを追加
        if(!($('.filename').length)){
            $('#js-selectFile').append('<span class="filename"></span>');
        };
        //ファイル名を表示
        $('.filename').html('ファイル名：' + file.name);
    });
});

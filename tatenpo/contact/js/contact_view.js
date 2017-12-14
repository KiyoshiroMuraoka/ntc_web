$(function() {
  $("#service-1").click(function() {
      $('#sourcebody').removeClass('invisible');
      $('#sourcebody2').removeClass('invisible').text('移行先モール');
      $('.chk_output,.q-row').removeClass('invisible');
      $('#row_url').removeClass('invisible');
      $('#title_url').text('移行元URL');
      $('#body').attr('placeholder', '例：移行商品約500件、来月のセールには間に合わせたいのですが、何日ぐらいかかりますか？')
  });
  $("#service-2").click(function() {
      $('#sourcebody').addClass('invisible');
      $('#sourcebody2').removeClass('invisible').text('登録先モール');
      $('.chk_output,.q-row').removeClass('invisible');
      $('#row_url').removeClass('invisible');
      $('#title_url').text('店舗URL');
      var chkvals = $('.c_output:checked').map(function() {
            return $(this).val().toString();
          }).get();
      if(chkvals.indexOf('rk') != -1 || chkvals.indexOf('bd') != -1){
        $('#csv-attention').removeClass('invisible');
      }else{
        $('#csv-attention').addClass('invisible');
      }
      $('#body').attr('placeholder', '例：月2回(隔週)で1回商品約100件を楽天、Yahoo、Wowmaの3店舗に登録したいのですが、1度に何商品まで登録可能ですか？')

  });
  $("#service-3").click(function() {
      $('#sourcebody2').removeClass('invisible').text('対象モール');
      $('.chk_output,.q-row').removeClass('invisible');
      $('#sourcebody,#output-2,#output-3,#output-5,#output-7,#demo').addClass('invisible');
      $('#row_url').removeClass('invisible');
      $('#title_url').text('店舗URL');
      $('#csv-attention').addClass('invisible');
      $('#body').attr('placeholder', '')

  });
  $('.r_source').on('change', function(){
    var service = $('input[name=service]:checked').val();
    if($(this).val() == 'base-rk' && service === 'ChangeOver' ){
      $('#csv-attention').removeClass('invisible');
    }else{
      $('#csv-attention').addClass('invisible');
    }
  });
  // 登録先モールチェック処理
  $('.c_output').on('change', function(){
    var service = $('input[name=service]:checked').val();
    var chkvals = $('.c_output:checked').map(function() {
          return $(this).val().toString();
        }).get();
    if(service === 'RegularPurchases' && (chkvals.indexOf('rk') != -1 || chkvals.indexOf('bd') != -1) ){
      $('#csv-attention').removeClass('invisible');
    }else{
      $('#csv-attention').addClass('invisible');
    }
  })
  jQuery("#form").validationEngine();
  switch ($('input[name=service]:checked').val()) {
    case 'ChangeOver':
      $('#sourcebody').removeClass('invisible');
      $('#sourcebody2').removeClass('invisible').text('移行先モール');
      $('.chk_output,.q-row').removeClass('invisible');
      $('#demo').addClass('invisible');
      $('#title_url').text('移行元URL');
      break;
    case 'RegularPurchases':
      $('#sourcebody').addClass('invisible');
      $('#sourcebody2').removeClass('invisible').text('登録先モール');
      $('.chk_output,.q-row').removeClass('invisible');
      $('#demo').removeClass('invisible');
      $('#title_url').text('店舗URL');
      var chkvals = $('.c_output:checked').map(function() {
            return $(this).val().toString();
          }).get();
      if(chkvals.indexOf('rk') != -1 || chkvals.indexOf('bd') != -1){
        $('#csv-attention').removeClass('invisible');
      }else{
        $('#csv-attention').addClass('invisible');
      }
      break;
    case 'HTTPS':
      $('#sourcebody2').removeClass('invisible').text('対象モール');
      $('.chk_output,.q-row').removeClass('invisible');
      $('#sourcebody,#output-2,#output-3,#output-5,#output-7,#demo').addClass('invisible');
      $('#title_url').text('店舗URL');
      $('#csv-attention').addClass('invisible');
      break;
    default:
  }
});

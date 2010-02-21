$(document).ready(function() {
  
  $('#login-form').insertAfter('#footer'); // move login form to end of document
  
  $('#login-trigger').click(function() {
    var offsetSubheader = $('#subheader').offset();
    var posX = parseInt(offsetSubheader.left) + $('#subheader').width() - $('#login-form').width();

    $('#login-form').css('left', posX).slideDown();
  });

  $('#close-login').click(function() { // close onClick login-form
    $('#login-form').slideUp();
  });
  
  $('#login-form input.inline-text').each(function(index) {
    if ($(this).val()) {
      $(this).prev('label').addClass('focus').hide();
    };
  });
  
  $('#login-form input').focus(function() { // hide label if input-field get focus
    $(this).prev('label').addClass('focus').hide();
  });

  $('#login-form input').blur(function() { // show label if imput-field is empty and hase no focus
    if ($(this).val() == "") {
      $('#login-form .focus').removeClass('focus').show();
    };
  });
  
});

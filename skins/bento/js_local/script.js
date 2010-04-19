$(document).ready(function() {
  
  var wikiUser = $('#pt-personal :first-child').addClass('wiki-user').html(); // Get Username
  var wikiLogout = $('#pt-personal li:last-child').addClass('logout').html(); // Get Username

  // $('#pt-personal li:first-child').addClass('hidden');
  $('#pt-personal li:first-child').remove();
  // $('#pt-personal li:last-child').addClass('hidden');
  $('#pt-personal li:last-child').remove();

  
  // console.log(wikiUser); // DEBUG
  // console.log(wikiLogout); // DEBUG
  // console.log(wikiActions); // DEBUG
  
  
  $('#pt-personal').before(wikiUser);
  $('#pt-personal').after(wikiLogout);
  
  $('#pt-personal').before('<a id="actions-trigger" href="#">Actions</a>'); // add trigger for useractions
  $('#pt-personal').insertAfter('#footer'); // move user-actions bihind #footer
  
  $('#actions-trigger').click(function() { // show || hide user actions
    
    var offset = $('#actions-trigger').offset();
    var x = offset.left - 13; // remove some extra px from offset; just for optical reasons
    var y = offset.top + 20; // add some extra px to offset, else it overlaps the title
    
    // console.log(y);
    
    $('#pt-personal').css({top: y, left: x}).slideToggle('fast'); // position of dropdown box
    
    return false;
  });
  
  
});

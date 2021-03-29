// Show/Hide sidebar on small screens
$('#main').prepend(`<button id="offcanvas-on" type="button"><span class="visually-hidden">${dotclear_magalogue.show_menu}</span></button>`);
$('#offcanvas-on').on('click', function() {
  const btn = $(`<button id="offcanvas-off" type="button"><span class="visually-hidden">${dotclear_magalogue.hide_menu}</span></button>`);
  $('#wrapper').addClass('off-canvas');
  $('#footer').addClass('off-canvas');
  $('#sidebar').prepend(btn);
  btn[0].focus({
    preventScroll: true
  });
  btn.on('click', function(evt) {
    $('#wrapper').removeClass('off-canvas');
    $('#footer').removeClass('off-canvas');
    evt.target.remove();
    $('#offcanvas-on')[0].focus();
  });
});

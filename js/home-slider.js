$(document).ready(function(){
const mediaQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
if (!mediaQuery || mediaQuery.matches) {
    var animate = false;
} else {
    var animate = true;
}
  $('#topshelf').slick({
    autoplay: animate,
    autoplaySpeed: 5000,
    dots: true
  });
});
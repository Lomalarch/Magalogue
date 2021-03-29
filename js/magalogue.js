// Collapsible categories
$(function(){
    $("#sidebar .categories > ul").treemenu({
        activeSelector: 'category-current',
        openActive: true,
        closeOther: true,
        delay: 300
    });
});
/*global $, dotclear */
'use strict';

const dotclear_magalogue = dotclear.getData('dotclear_magalogue');

$("html").addClass("js");
// Show/Hide main menu
if (screen.width<=1280) {
    $(".header__nav").
    before(`<button id="hamburger" type="button"><span class="visually-hidden">${dotclear_magalogue.navigation}</span></button>`).toggle();
    $(".follow-us").toggle();
    $("#hamburger").click(function() {
        $(this).toggleClass("open");
        $(".header__nav, .follow-us").toggle('easing');
    });
}
$(document).ready(function() {
    // totop scroll
    $(window).scroll(function() {
        if ($(this).scrollTop() != 0) {
            $('#gotop').fadeIn();
        } else {
            $('#gotop').fadeOut();
        }
    });
    $('#gotop').click(function(e) {
        $('body,html').animate({
            scrollTop: 0
        }, 800);
        e.preventDefault();
    });
    // suppress linked image border
    $('a:has(>img)').each(function() {
        $(this).css('border', 'none');
    });
});
var stickyOffset = $('.header').offset().top;
$(window).scroll(function(){
    scroll = $(window).scrollTop();
});


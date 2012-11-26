/*
 * Text Limit jQuery Plugin
 * Author: Vinicius Massuchetto
 * URL: http://vinicius.soylocoporti.org.br/text-limit-jquery-plugin
 */

(function($){

var acfl;

function colorize (obj, length) {

    countbox = $(obj);
    threshold1 = length / 2;
    threshold2 = length / 4;
    n = parseInt(countbox.html(), 10);

    if (n < threshold2) {
        countbox.removeClass('count-box-threshold1');
        countbox.addClass('count-box-threshold2');
    } else if (n < threshold1) {
        countbox.addClass('count-box-threshold1');
    } else if (countbox.hasClass('count-box-threshold1') || countbox.hasClass('count-box-threshold2')) {
        countbox.removeClass('count-box-threshold1');
        countbox.removeClass('count-box-threshold2');
    }

}

$.fn.textlimit = function (length) {

    this.css('padding-right', '40px');
    countbox = $('<span>');
    countbox.addClass('count-box');
    countbox.addClass(countclass = this.selector.replace(/[^0-9A-Za-z]/, '').replace(' ', '-') + '-count');
    var n = 0;
    if (this.is('input')) {
        n = this.val().length;
    } else if (this.is('textarea')) {
        n = this.text().length;
    }
    n = length - n;
    countbox.html(n);
    this.parent().append(countbox);
    colorize(countbox, length);

    this.keyup(function(){
        obj = $(this);
        n = obj.val().length;
        if (n > length)
            obj.val(obj.val().substring(0, length));
        else {
            countbox = obj.parent().children('.count-box');
            countbox.html(length - n);
            colorize(countbox, length);
        }
    });

};

})(jQuery);

jQuery(document).ready(function(){
    for (var f in acfl) {
        jQuery(f).textlimit(acfl[f]);
    }
});

/* 
 Created on : Nov 24, 2017, 10:59:11 AM
 Author     : gregzorb
 */

function setActiveMenuItem()
{
    $('.nav.nav-pills a.nav-link').removeClass('active');
    var urlVars = getUrlVars();
    if (urlVars['action']) {
        $('.nav.nav-pills a.nav-link.page-' + urlVars['action']).addClass('active');
    }
}


function getUrlVars()
{
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

$('document').ready(function () {
    setActiveMenuItem();
});

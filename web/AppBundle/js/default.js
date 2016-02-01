/**
 * Fichier js commun à tout le site
 */
$(document).ready(function () {
    var institutionCode = $("#parameters").data("institutioncode") ;
    var collectionCode = $("#parameters").data("collectioncode") ;
    $("#menu-toggle").click(function (e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
        $(this).toggleClass("collapsed");
    });


    if (localStorage.getItem('selectedSpecimens')) {
        selectedSpecimens = JSON.parse(localStorage.getItem('selectedSpecimens'));
    }

    // Checked selected Specimens
    nbSelectedSpecimens = selectedSpecimens.length;
    if (nbSelectedSpecimens > 0 && $("#linkSelectedSpecimen").length == 1 && institutionCode !='' && collectionCode !='') {
        url = Routing.generate('viewSpecimens', {institutionCode: institutionCode, collectionCode : collectionCode, jsonSpecimensCode : localStorage.getItem('selectedSpecimens')}) ;
        $("#linkSelectedSpecimen").attr("href", url).removeClass("hidden") ;
        console.log(textSelectSpecimen);
        if (nbSelectedSpecimens == 1) {
            $("#linkSelectedSpecimen").html(textSelectSpecimen) ;
        }
        else {
            $("#linkSelectedSpecimen").html(textSelectSpecimens.replace('%count%', nbSelectedSpecimens)) ;
        }
    }
});
/**
 * Fichier js commun à tout le site
 */
$(document).ready(function () {
    var modalSpinner = $("#spinnerModal");

    $.toaster({
        settings: {
            'timeout': 5000,
            'toaster': {
                'css': {
                    'position': 'fixed',
                    'top': '10px',
                    'right': '10px',
                    'width': '350px',
                    'zIndex': 50000
                }
            }
        }
    });
    $(document)
        .ajaxStart(function () {
            modalSpinner.modal('show');
        })
        .ajaxStop(function () {
            modalSpinner.modal('hide');
        })
        .ajaxComplete(function (event, xhr, settings) {
            try {
                var data = $.parseJSON(xhr.responseText);

                if (data.messages) {
                    var messages = data.messages;

                    var i;

                    if (messages.warning) {
                        for (i = 0; i < messages.warning.length; i++) {
                            $.toaster({
                                message: messages.warning[i],
                                priority: 'warning',
                                title: Translator.trans('label.warning')
                            });
                        }
                    }

                    if (messages.error) {
                        for (i = 0; i < messages.error.length; i++) {
                            $.toaster({
                                message: messages.error[i],
                                priority: 'danger',
                                title: Translator.trans('label.danger')
                            });
                        }
                    }

                    if (messages.success) {
                        for (i = 0; i < messages.success.length; i++) {
                            $.toaster({
                                message: messages.success[i],
                                priority: 'success',
                                title: Translator.trans('label.success')
                            });
                        }
                    }

                    if (messages.info) {
                        for (i = 0; i < messages.info.length; i++) {
                            $.toaster({
                                message: messages.info[i],
                                priority: 'info',
                                title: Translator.trans('label.info')
                            });
                        }
                    }
                }
            } catch (e) {

            }
        });
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
        if (nbSelectedSpecimens == 1) {
            $("#linkSelectedSpecimen").html(textSelectSpecimen) ;
        }
        else {
            $("#linkSelectedSpecimen").html(textSelectSpecimens.replace('%count%', nbSelectedSpecimens)) ;
        }
    }
});
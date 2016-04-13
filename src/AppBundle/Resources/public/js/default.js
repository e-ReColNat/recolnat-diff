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
    var $parameters = $("#parameters");
    var institutionCode = $parameters.data("institutioncode") ;
    var collectionCode = $parameters.data("collectioncode") ;
    var $checkboxSpecimen = $(".js_specimen").find("[name^='check-specimen']");

    var selectedSpecimens=[];
    if (localStorage.getItem('selectedSpecimens') !== null) {
        selectedSpecimens = JSON.parse(localStorage.getItem('selectedSpecimens'));
    }

    // Checked selected Specimens
    var nbSelectedSpecimens = selectedSpecimens.length;
    var $linkSelectedSpecimen = $("#linkSelectedSpecimen");
    setLinkViewSelected();

    function setLinkViewSelected() {
        var url = Routing.generate('viewSpecimens', {
            institutionCode: institutionCode,
            collectionCode: collectionCode,
            jsonCatalogNumbers: localStorage.getItem('selectedSpecimens')
        });
        if (nbSelectedSpecimens == 0) {
            $linkSelectedSpecimen.attr("href", url).addClass("hidden");
        }
        else {
            $linkSelectedSpecimen.attr("href", url).removeClass("hidden");
        }
        if (nbSelectedSpecimens == 1) {
            $linkSelectedSpecimen.html(Translator.trans('viewSelectedSpecimen'));
        }
        else {
            $linkSelectedSpecimen.html(Translator.trans('viewSelectedSpecimens').replace('%count%', nbSelectedSpecimens));
        }
    }

    if (nbSelectedSpecimens > 0 && $linkSelectedSpecimen.length == 1 && institutionCode !='' && collectionCode !='') {
        setLinkViewSelected();
    }
    if (nbSelectedSpecimens > 0) {
        for (var i = 0; i < nbSelectedSpecimens; i++) {
            $checkboxSpecimen.filter('[value="' + selectedSpecimens[i] + '"]').prop('checked', true);
        }
    }

    // Sélection d'un specimen manuellement
    $checkboxSpecimen.change(function () {
        if ($(this).prop('checked')) {
            selectedSpecimens.push($(this).val());
        }
        else {
            for (var i = selectedSpecimens.length - 1; i >= 0; i--) {
                if (selectedSpecimens[i] === $(this).val()) {
                    selectedSpecimens.splice(i, 1);
                }
            }
        }
        localStorage.setItem('selectedSpecimens', JSON.stringify(selectedSpecimens));
        nbSelectedSpecimens = selectedSpecimens.length;
        setLinkViewSelected();
    });


});

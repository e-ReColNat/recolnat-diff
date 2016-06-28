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
    var institutionCode = $parameters.data("institutionCode") ;
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

    if (nbSelectedSpecimens > 0 && $linkSelectedSpecimen.length == 1 && collectionCode !='') {
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

    $('.js-form-selectedSpecimen').on('submit', function(e) {
        e.preventDefault();
        var button = $(this).children('button').first();

        $.ajax({
            url: $(this).attr('action'),
            data: {action : button.attr('name'), catalogNumber : button.attr('value')},
            method: "POST",
            global: false
        })
        .done(function (data) {
            var arr = $.map(data, function(el) { return el });
            if (jQuery.inArray(button.attr('value'), arr) > 0) {
                button.attr('name', 'remove');
                button.children('span').removeClass('glyphicon-star-empty').addClass('glyphicon-star text-primary');
            }
            else {
                button.attr('name', 'add');
                button.children('span').addClass('glyphicon-star-empty').removeClass('glyphicon-star text-primary');
            }
        });
    });

    var boolScrollToHash = true;
    var offsetTopContent = $('.navbar-fixed-top').height() + parseInt($('.navbar-fixed-top').css('margin-bottom'), 10);

    // Gestion des onglets
    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {

        boolScrollToHash = false;
        var currTabTarget = $(e.target).attr('href');

        var remoteUrl = $(this).attr('data-tab-remote');
        var loadedOnce = $(this).data('loaded');
        if (remoteUrl !== '' && !loadedOnce) {
            $(currTabTarget).load(remoteUrl);
            $(this).data('loaded', true);
        }
        boolScrollToHash = true;
    });

    // Gestion du décalage du à la barre de menu statique
    function maybeScrollToHash() {
        // Permet de placer le scroll au bon endroit en prenant en compte la barre de menu fixe
        var $hash = $(window.location.hash);
        if (window.location.hash && $hash.length && boolScrollToHash) {
            var newTop = $hash.offset().top - offsetTopContent;
            $(window).scrollTop(newTop);
        }
    }

    $(window).bind('hashchange', function (e) {
        target = $(window.location.hash);
        // Pour éviter d'appeler la fonction en cliquant sur les lettres de la sidebar
        if (! target.hasClass('js-sidebar-anchors')) {
            e.preventDefault();
            maybeScrollToHash();
        }
    });
    maybeScrollToHash();


    $(document).on("scroll", onScroll);
    // scroll la sidebar et met en valeur le lien de la sidebar lorsqu'on scrolle dans la liste des différences
    function onScroll(event)
    {
        var scrollPos = $(document).scrollTop() - offsetTopContent;
        var specimen = $("#diffs").find(".js_specimen").filter(function () {
            if ($(this).position().top <= scrollPos && $(this).position().top + $(this).height() > scrollPos) {
                return true;
            }
        });
        if (specimen) {
            var catalogNumber = specimen.attr('id');
            var sb_specimen = $('.sidebar-choices li#js_sb_' + catalogNumber);

            if (sb_specimen.length == 1) {
                $('.sidebar-choices li').removeClass("active");
                sb_specimen.addClass("active");
            }
        }

    }
});

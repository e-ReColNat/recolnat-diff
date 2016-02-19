$(document).ready(function () {
    var boolScrollToHash = true;
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

    function maybeScrollToHash() {
        // Permet de placer le scroll au bon endroit en prenant en compte la barre de menu fixe
        var $hash = $(window.location.hash);
        if (window.location.hash && $hash.length && boolScrollToHash) {
            var newTop = $hash.offset().top + parseInt($hash.css('padding-top'), 10) - $('.navbar-fixed-top').height() - parseInt($('body').css('padding-top'), 10) + parseInt($('.navbar-fixed-top').css('margin-bottom'), 10);
            $(window).scrollTop(newTop);
        }
    }

    $(window).bind('hashchange', function (e) {
        e.preventDefault();
        maybeScrollToHash();
    });


    maybeScrollToHash();


    var $diffs = $('#diffs');
    var institutionCode = $diffs.data('institutioncode');
    var collectionCode = $diffs.data('collectioncode');
    var selectedClassName = $diffs.data('selectedclassname');
    var smallModal = $('#smallModal');
    var selectedSpecimens = [];
    var highlightClass = 'highlight';
    if (localStorage.getItem('selectedSpecimens')) {
        selectedSpecimens = JSON.parse(localStorage.getItem('selectedSpecimens'));
    }
    var maxItemPerPage = $('#maxItemPerPage').find(":selected").text();
    if (localStorage.getItem('maxItemPerPage')) {
        maxItemPerPage = localStorage.getItem('maxItemPerPage');
    }

    // Checked selected Specimens
    var nbSelectedSpecimens = selectedSpecimens.length;
    var $specimen = $(".specimen");
    if (nbSelectedSpecimens > 0) {
        var checkboxSpecimen = $specimen.find("[name^='check-specimen']");
        for (var i = 0; i < nbSelectedSpecimens - 1; i++) {
            checkboxSpecimen.filter('[value="' + selectedSpecimens[i] + '"]').prop('checked', true);
        }
    }

    // Rajoute les choix effectués au tableau choices
    function setChoice(choices, element, specimenCode) {
        var fieldName = element.data('fieldname');
        var className = element.data('class');
        var relationId = element.data('relationid');
        var choice = {
            'className': className,
            'fieldName': fieldName,
            'relationId': relationId,
            'choice': element.attr('value'),
            'specimenCode': specimenCode
        };
        var flag = false;
        if (choices.length > 0) {
            for (var i = 0; i < choices.length; i++) {
                var row = choices[i];
                if (
                    row.className === choice.className &&
                    row.fieldName === choice.fieldName &&
                    row.relationId === choice.relationId
                ) {
                    flag = true;
                    choices[i] = choice;
                }
            }
        }
        if (!flag) {
            choices.push(choice);
        }
    }

    // Met à jour l'affichage de la liste des specimens en fonction du retour du serveur
    function updateDisplay(data) {
        var $idFacet = $("[id^='facet-']");
        $idFacet.data('comptchoices', 0);
        var comptchoices = [];
        var choices = data.choices;
        $.each(data.choices, function (i) {
            $("input[type=radio][data-relationid='" + choices[i]['relationId'] + "'][data-fieldname='" + choices[i]['fieldName'] + "'][value=" + choices[i]['choice'] + "]").prop('checked', true);
            if (typeof comptchoices[choices[i]['specimenCode']] === "undefined") {
                comptchoices[choices[i]['specimenCode']] = [];
            }
            if (typeof comptchoices[choices[i]['specimenCode']][choices[i]['className']] === "undefined") {
                comptchoices[choices[i]['specimenCode']][choices[i]['className']] = 1;
            }
            else {
                comptchoices[choices[i]['specimenCode']][choices[i]['className']]++;
            }
            $("#facet-" + choices[i]['specimenCode'] + "-" + choices[i]['className']).data('comptchoices', comptchoices[choices[i]['specimenCode']][choices[i]['className']]);
        });
        $idFacet.each(function () {
            var formattedTemplate = formatTemplate($(this).find('.facet-className').html(), $(this).data('comptchoices'), $(this).data('comptdiffs'));
            $(this).html(formattedTemplate);
            if ($(this).data('comptchoices') === $(this).data('comptdiffs')) {
                $(this).removeClass('text-warning').addClass('text-success');
            }
        });
    }

    // Renvoie le template mis à jour d'un specimen de la liste
    function formatTemplate(className, comptChoices, comptDiffs) {
        var spanChoices;
        var spanDiffs;
        if (comptChoices > 1) {
            spanChoices = $("#template-facet-string > .template-facet-choices-plural").html();
        }
        else {
            spanChoices = $("#template-facet-string > .template-facet-choices-single").html();
        }
        if (comptDiffs > 1) {
            spanDiffs = $("#template-facet-string > .template-facet-diffs-plural").html();
        }
        else {
            spanDiffs = $("#template-facet-string > .template-facet-diffs-single").html();
        }
        var choicesFormatted = spanChoices.format(comptChoices);
        var diffsFormatted = spanDiffs.format(comptDiffs);
        var facetTemplate = $("#template-facet").html();
        return facetTemplate.format(className, choicesFormatted, diffsFormatted);
    }

    // Sélection bouton radio par specimen
    $('table.diff').find(":radio").change(function () {
            var choices = [];
            var tableContext = $(this).parents('table.diff');
            var relationId = $(this).attr('name');
            var choice = $(this).attr('value');
            var specimenCode = $(this).parents('section').data('specimencode');
            if ($(this).data('type') === 'diff-entity') {
                tableContext.find(":radio")
                    .filter("[name^='" + relationId + "']")
                    .filter('[data-type="diff-field"]')
                    .filter('[value = "' + choice + '"]')
                    .map(
                        function () {
                            $(this).prop('checked', true);
                            setChoice(choices, $(this), specimenCode);
                        });
            }
            else {
                setChoice(choices, $(this), specimenCode);
            }
            $.ajax({
                    url: Routing.generate('setChoice', {institutionCode: institutionCode, collectionCode: collectionCode}),
                    data: {'choices': choices},
                    method: "POST"
                })
                .done(function (data) {
                    updateDisplay(data);
                });
        })
        // Mise en exergue des valeurs concernés par le bouton radio
        .hover(function () {
            if ($(this).data('type') == 'diff-field') {
                higlightCells($(this), highlightClass);
            }
            if ($(this).data('type') == 'diff-entity') {
                var tableContext = $(this).parents('table.diff');
                var relationId = $(this).attr('name');
                var choice = $(this).attr('value');
                tableContext.find(":radio")
                    .filter("[name^='" + relationId + "']")
                    .filter('[data-type="diff-field"]')
                    .filter('[value = "' + choice + '"]')
                    .map(
                        function () {
                            higlightCells($(this), highlightClass);
                        });
            }

        }, function () {
            if ($(this).data('type') == 'diff-field') {
                unHiglightCells($(this), highlightClass);
            }
            if ($(this).data('type') == 'diff-entity') {
                var tableContext = $(this).parents('table.diff');
                var relationId = $(this).attr('name');
                var choice = $(this).attr('value');
                tableContext.find(":radio")
                    .filter("[name^='" + relationId + "']")
                    .filter('[data-type="diff-field"]')
                    .filter('[value = "' + choice + '"]')
                    .map(
                        function () {
                            unHiglightCells($(this), highlightClass);
                        });
            }

        });

    function higlightCells(radioElement, highlightClass) {
        if (radioElement.length > 0) {
            radioElement.parent().addClass(highlightClass);
            radioElement.parent().prev().addClass(highlightClass);
        }
    }

    function unHiglightCells(radioElement, highlightClass) {
        if (radioElement.length > 0) {
            radioElement.parent().removeClass(highlightClass);
            radioElement.parent().prev().removeClass(highlightClass);
        }
    }

    // Filtres  généraux
    $("#form-filters").submit(function (event) {
        var data = $(this).serializeArray();
        data.push({name: 'maxItemPerPage', value: maxItemPerPage});
        if ($("[name='specimens']").filter(":checked").val() === 'selectedSpecimens') {
            if (selectedSpecimens.length === 0) {
                smallModal.find('.modal-title').text('Erreur');
                smallModal.find('.modal-body').text('Vous devez sélectionner au moins un spécimen');
                smallModal.modal('show');
                return false;
            }
            else {
                data.push({name: 'selectedSpecimens', value: JSON.stringify(selectedSpecimens)});
            }
        }

        $.ajax({
                url: Routing.generate('setChoices', {institutionCode: institutionCode, collectionCode: collectionCode}),
                data: data,
                method: "POST"
            })
            .done(function (data) {
                updateDisplay(data);
            });
        event.preventDefault();
    });

    // Sélection d'un specimen manuellement
    $specimen.find("[name^='check-specimen']").change(function () {
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
    });
});
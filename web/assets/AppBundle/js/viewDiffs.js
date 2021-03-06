$(document).ready(function () {

// Bouton de recherche
    $(".btn-search").click(function (event) {
        var $searchInput = $("#js-search-input");
        var $searchForm = $("#js-search-form");
        if ($(this).hasClass('active') && $searchInput.val() == '') {
            $searchForm.removeClass('active') ;
            $(this).removeClass('active') ;
        }
        else {
            if ($searchInput.val() !== '') {
                $searchForm.submit();
            }
            $searchForm.addClass('active');
            $(this).addClass('active');
        }
        event.preventDefault();
    });

    var $diffs = $('#diffs');
    var institutionCode = $("#parameters").data('institutioncode');
    var collectionCode = $("#parameters").data('collectioncode');
    var selectedClassName = $diffs.data('selectedclassname');
    var smallModal = $('#smallModal');
    var selectedSpecimens = [];
    if (localStorage.getItem('selectedSpecimens') !== null) {
        selectedSpecimens = JSON.parse(localStorage.getItem('selectedSpecimens'));
    }
    var highlightClass = 'highlight';
    var $specimen = $(".specimen");

    var maxItemPerPage = $('#maxItemPerPage').find(":selected").text();
    if (localStorage.getItem('maxItemPerPage')) {
        maxItemPerPage = localStorage.getItem('maxItemPerPage');
    }

    // Rajoute les choix effectués au tableau choices
    function setChoice(choices, element, catalogNumber) {
        var fieldName = element.data('fieldname');
        var className = element.data('class');
        var relationId = element.data('relationid');
        var choice = {
            'className': className,
            'fieldName': fieldName,
            'relationId': relationId,
            'choice': element.attr('value'),
            'catalogNumber': catalogNumber
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
            if (typeof comptchoices[choices[i]['catalogNumber']] === "undefined") {
                comptchoices[choices[i]['catalogNumber']] = [];
            }
            if (typeof comptchoices[choices[i]['catalogNumber']][choices[i]['className']] === "undefined") {
                comptchoices[choices[i]['catalogNumber']][choices[i]['className']] = 1;
            }
            else {
                comptchoices[choices[i]['catalogNumber']][choices[i]['className']]++;
            }
            $("#facet-" + choices[i]['catalogNumber'] + "-" + choices[i]['className']).data('comptchoices', comptchoices[choices[i]['catalogNumber']][choices[i]['className']]);
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
    $('table.diff').find(".js_select_entity").hover(function () {
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
    }, function () {

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
    }).click(function() {
        var choices = [];
        var tableContext = $(this).parents('table.diff');
        var relationId = $(this).attr('name');
        var choice = $(this).attr('value');
        var catalogNumber = $(this).parents('.js_specimen').data('catalognumber');
        tableContext.find(":radio")
            .filter("[name^='" + relationId + "']")
            .filter('[data-type="diff-field"]')
            .filter('[value = "' + choice + '"]')
            .map(
                function () {
                    $(this).prop('checked', true);
                    setChoice(choices, $(this), catalogNumber);
                });
        $.ajax({
                url: Routing.generate('setChoice', { institutionCode: institutionCode, collectionCode: collectionCode}),
                data: {'choices': choices},
                method: "POST"
            })
            .done(function (data) {
                updateDisplay(data);
            });
    });

    $('table.diff').find(":radio").change(function () {
            var choices = [];
            var tableContext = $(this).parents('table.diff');
            var relationId = $(this).attr('name');
            var choice = $(this).attr('value');
            var catalogNumber = $(this).parents('.js_specimen').data('catalognumber');
            if ($(this).data('type') === 'diff-entity') {
                tableContext.find(":radio")
                    .filter("[name^='" + relationId + "']")
                    .filter('[data-type="diff-field"]')
                    .filter('[value = "' + choice + '"]')
                    .map(
                        function () {
                            $(this).prop('checked', true);
                            setChoice(choices, $(this), catalogNumber);
                        });
            }
            else {
                setChoice(choices, $(this), catalogNumber);
            }
            $.ajax({
                    url: Routing.generate('setChoice', { institutionCode: institutionCode, collectionCode: collectionCode}),
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
            radioElement.parents('td').addClass(highlightClass);
        }
    }

    function unHiglightCells(radioElement, highlightClass) {
        if (radioElement.length > 0) {
            radioElement.parents('td').removeClass(highlightClass);
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
                url: Routing.generate('setChoices', { institutionCode: institutionCode, collectionCode: collectionCode}),
                data: data,
                method: "POST"
            })
            .done(function (data) {
                updateDisplay(data);
            });
        event.preventDefault();
    });


    $(".sidebar-btn").click(function (e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
        $(this).toggleClass("collapsed");
    });
    /** Gestion sidebar et filtres */
    $('#js_filters').on('show.bs.collapse', function () {
        $('#wrapper').addClass('toggled');
        $('#menu-toggle').addClass('collapsed');
    });
    $('#menu-toggle').on('click', function () {
        $('#js_filters').collapse('hide');
    });
    $("#selectAllClasses").click(function (event) {
        $("#form-filters").find("[name^='classesName']").not($(this)).prop('checked', true);
        event.preventDefault();
    });
});

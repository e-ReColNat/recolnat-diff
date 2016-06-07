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

/***********************************************************************************
 * Add Array.indexOf                                                                *
 ***********************************************************************************/
(function ()
{
    if (typeof Array.prototype.indexOf !== 'function')
    {
        Array.prototype.indexOf = function (searchElement, fromIndex)
        {
            for (var i = (fromIndex || 0), j = this.length; i < j; i += 1)
            {
                if ((searchElement === undefined) || (searchElement === null))
                {
                    if (this[i] === searchElement)
                    {
                        return i;
                    }
                } else if (this[i] === searchElement)
                {
                    return i;
                }
            }
            return -1;
        };
    }
})();
/**********************************************************************************/

(function ($, undefined)
{
    var toasting =
            {
                gettoaster: function ()
                {
                    var toaster = $('#' + settings.toaster.id);

                    if (toaster.length < 1)
                    {
                        toaster = $(settings.toaster.template).attr('id', settings.toaster.id).css(settings.toaster.css).addClass(settings.toaster['class']);

                        if ((settings.stylesheet) && (!$("link[href=" + settings.stylesheet + "]").length))
                        {
                            $('head').appendTo('<link rel="stylesheet" href="' + settings.stylesheet + '">');
                        }

                        $(settings.toaster.container).append(toaster);
                    }

                    return toaster;
                },
                notify: function (title, message, priority)
                {
                    var $toaster = this.gettoaster();
                    var $toast = $(settings.toast.template.replace('%priority%', priority)).hide().css(settings.toast.css).addClass(settings.toast['class']);

                    $('.title', $toast).css(settings.toast.csst).html(title);
                    $('.message', $toast).css(settings.toast.cssm).html(message);

                    if ((settings.debug) && (window.console))
                    {
                        console.log(toast);
                    }

                    $toaster.append(settings.toast.display($toast));

                    if (settings.donotdismiss.indexOf(priority) === -1)
                    {
                        var timeout = (typeof settings.timeout === 'number') ? settings.timeout : ((typeof settings.timeout === 'object') && (priority in settings.timeout)) ? settings.timeout[priority] : 1500;
                        setTimeout(function ()
                        {
                            settings.toast.remove($toast, function ()
                            {
                                $toast.remove();
                            });
                        }, timeout);
                    }
                }
            };

    var defaults =
            {
                'toaster':
                        {
                            'id': 'toaster',
                            'container': 'body',
                            'template': '<div></div>',
                            'class': 'toaster',
                            'css':
                                    {
                                        'position': 'fixed',
                                        'top': '10px',
                                        'right': '10px',
                                        'width': '300px',
                                        'zIndex': 50000
                                    }
                        },
                'toast':
                        {
                            'template':
                                    '<div class="alert alert-%priority% alert-dismissible" role="alert">' +
                                    '<button type="button" class="close" data-dismiss="alert">' +
                                    '<span aria-hidden="true">&times;</span>' +
                                    '<span class="sr-only">Close</span>' +
                                    '</button>' +
                                    '<span class="title"></span>: <span class="message"></span>' +
                                    '</div>',
                            'defaults':
                                    {
                                        'title': 'Notice',
                                        'priority': 'success'
                                    },
                            'css': {},
                            'cssm': {},
                            'csst': {'fontWeight': 'bold'},
                            'fade': 'slow',
                            'display': function ($toast)
                            {
                                return $toast.fadeIn(settings.toast.fade);
                            },
                            'remove': function ($toast, callback)
                            {
                                return $toast.animate(
                                        {
                                            opacity: '0',
                                            padding: '0px',
                                            margin: '0px',
                                            height: '0px'
                                        },
                                        {
                                            duration: settings.toast.fade,
                                            complete: callback
                                        }
                                );
                            }
                        },
                'debug': false,
                'timeout': 1500,
                'stylesheet': null,
                'donotdismiss': []
            };

    var settings = {};
    $.extend(settings, defaults);

    $.toaster = function (options)
    {
        if (typeof options === 'object')
        {
            if ('settings' in options)
            {
                settings = $.extend(true, settings, options.settings);
            }
        } else
        {
            var values = Array.prototype.slice.call(arguments, 0);
            var labels = ['message', 'title', 'priority'];
            options = {};

            for (var i = 0, l = values.length; i < l; i += 1)
            {
                options[labels[i]] = values[i];
            }
        }

        var title = (('title' in options) && (typeof options.title === 'string')) ? options.title : settings.toast.defaults.title;
        var message = ('message' in options) ? options.message : null;
        var priority = (('priority' in options) && (typeof options.priority === 'string')) ? options.priority : settings.toast.defaults.priority;

        if (message !== null)
        {
            toasting.notify(title, message, priority);
        }
    };

    $.toaster.reset = function ()
    {
        settings = {};
        $.extend(settings, defaults);
    };
})(jQuery);

String.prototype.format = function() {
  var args = arguments;
  return this.replace(/{(\d+)}/g, function(match, number) { 
    return typeof args[number] != 'undefined'
      ? args[number]
      : match
    ;
  });
};
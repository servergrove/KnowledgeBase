(function ($) {
    'use strict';

    var window = this;

    $(window.document).ready(function () {
        var handleSelector = function (checkbox) {
            var parent = $(checkbox).parent('.add-on');
            if ($(checkbox).is(':checked')) {
                parent.addClass('active');
                parent.siblings('input,select,textarea').each(function () {
                    $(this).removeAttr("disabled");
                });
            } else {
                parent.removeClass('active');
                parent.siblings('input:not(:checkbox),select,textarea').each(function () {
                    $(this).attr({disabled: "disabled"});
                });
            }
        };

        $('.input-prepend .add-on :checkbox').click(function () {
            handleSelector(this);
        });

        handleSelector($(':checkbox.enabler'));

        $('#uploaderModal')
            .on('hidden', function () {
                $('#galleryModal').modal('show');
            })
            .on('show', function () {
                $('#galleryModal').modal('hide');
            });

//    getAlertToolInstance().render();

        // URL modal launcher
        $(window.document.createElement('a')).addClass('btn btn-mini').attr({
            "data-toggle": "modal",
            "href": "#url-form-modal"
        }).html("Register URL").insertAfter('select.url-selector').wrap('<div></div>');

        // URL form
        $('form#article_url_form').submit(function () {
            var form = this;
            $(':button', form).addClass('disabled');

            $.ajax({
                url: $(form).attr('action'),
                type: $(form).attr('method'),
                data: $(form).serialize(),
                success: function (response) {
                    $(':button', form).removeClass('disabled');
                    if (response.result) {
                        $('form#article_form select#article_urls').append(new Option(response.rsp.name, response.rsp.id));
                    }

                    $('.modal').modal('hide');
                },
                error: function (result) {
                    $(':button', form).removeClass('disabled');
                    if (400 === result.status) {
                        var response = JSON.parse(result.responseText), fields;

                        if ('undefined' === typeof response.result) {
                            return;
                        }

                        fields = $("form#article_url_form").serializeArray();
                        $.each(fields, function (i, field) {
                            var pattern = /urls\[([\w]+)\]/, matches, name;
                            matches = field.name.match(pattern);

                            if ('undefined' !== typeof matches[1]) {
                                name = matches[1];
                                $('#urls_' + name).each(function () {
                                    $(this).siblings('.help-inline').remove();
                                    $(this).parent().parent('.control-group').removeClass('error');

                                    if ('undefined' !== typeof response.errors[name]) {
                                        $(this).parent().parent('.control-group').addClass('error');
                                        $(window.document.createElement('span')).addClass('help-inline').html(response.errors[name]).insertAfter('#urls_' + name);
                                    }
                                });
                            }
                        });
                    }
                }
            });

            return false;
        });

        $('.f16 .flag').tooltip({});

        (function () {
            var modified = false;

            $('.exit-control').each(function () {
                $(this).submit(function () {
                    modified = false;
                });

                $('textarea', this).change(function () {
                    modified = true;
                });
            });

            $(window).on('beforeunload', function () {
                if (modified) {
                    return 'You have some unsaved changes?';
                }

                return null;
            });
        }());
    });
}.call(window, window.jQuery));

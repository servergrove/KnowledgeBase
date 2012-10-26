(function ($, angular) {
    "use strict";

    angular.module('ServerGroveKb', []);

    $(document).ready(function () {


        $('select[switch="locale"]').change(function () {
            window.location.href = $('option:selected', this).attr('path');
        });


        $('.article-content pre code').each(function (i, e) {
            $(e).addClass('prettyprint');
        });

        prettyPrint();
    });

}(window.jQuery, window.angular));


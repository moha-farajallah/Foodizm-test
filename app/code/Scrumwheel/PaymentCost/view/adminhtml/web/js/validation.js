requirejs([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function($){
    'use strict';
    $.validator.addMethod(
        "validateDuplicateValue",
        function(value, element) {
            return false;
        },
        $.mage.__("This Payment Method is already selected.")
    );
});
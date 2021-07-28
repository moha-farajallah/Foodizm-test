/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

var config = {
    paths: {
        'slick': 'Intenso_Review/js/slick.min'
    },
    shim: {
        'slick': {
            deps: ['jquery'],
            exports: 'jQuery.fn.slick',
        }
    }
};

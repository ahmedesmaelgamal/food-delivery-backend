"use strict";

$('.action-installation-currency-select').on('change', function () {
    let value = $(this).val();
    if(value === 'multi_currency'){
        toastMagic.warning("Multi-currency is depends on exchange rate and your gateway configuration, So if you don't need multi-currency it will be better select single currency. (We prefer to use single currency).");
    }
})

$('.action-installation-show-loader').on('click', function () {
    $('#loading').fadeIn();
})

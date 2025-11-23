
$('#update-settings').on('submit', function (e) {
    let minimum_add_fund_amount = parseFloat($('#minimum_add_fund_amount').val());
    let maximum_add_fund_amount = parseFloat($('#maximum_add_fund_amount').val());
    if (maximum_add_fund_amount < minimum_add_fund_amount) {
        e.preventDefault();
        toastMagic.error($('#get-minimum-amount-message').data('error'));
    }
});

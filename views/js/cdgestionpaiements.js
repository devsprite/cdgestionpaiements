$(document).ready(function() {

    var nombreEcheances = $('#nombreEcheances');
    var totalPaid = total_paid;
    var ordersTotalPaidTaxIncl = orders_total_paid_tax_incl;
    var resteAPayer = ordersTotalPaidTaxIncl - totalPaid;
    var link = adminGestionPaiementsController + "&action=UpdateNumberOfEcheance&ajax=1";

    console.info("nombreEcheances = " + nombreEcheances.val());
    console.info("total_paid = " + total_paid);
    console.info("orders_total_paid_tax_incl = " + orders_total_paid_tax_incl);
    console.info("employeeIdProfile = " + employeeIdProfile);

    updateDisplay();

    // Input select number of echeance
    nombreEcheances.change(function(evt) {
        var numberEcheance = evt.target.value;
        console.info("Change nombreEcheances = " + numberEcheance);

        $.ajax({
            type: "post",
            url: link,
            data: {},
            success : function(data) {
                console.log("success");
            },
            error : function() {
                console.log("error");
            }


        });

        updateDisplay();
    });

    function updateDisplay() {
        $("#totalAPayer").text(formatPrice(ordersTotalPaidTaxIncl));
        $("#totalDejaPaye").text(formatPrice(totalPaid));
        $("#resteAPayer").text(formatPrice(resteAPayer));
    }

    function formatPrice(price){
        return new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(price);
    }

});
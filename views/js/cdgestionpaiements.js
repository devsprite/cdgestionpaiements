$(document).ready(function () {

    var total_paid;
    var orders_total_paid_tax_incl;
    var order_reste_a_payer;
    var paymentsNumber;
    var numberEcheance;
    var accompte;
    var loader = $(".loader");

    var linkOrderInformations = adminGestionPaiementsController + "&action=GetOrderInformations&ajax=1";
    var linkSetAccompte = adminGestionPaiementsController + "&action=SetAccompte&ajax=1";
    var idOrder = id_order;

    initEcheancier();

    $("#getOrderInformation").click(function () {
        loader.toggle(true);
        $.ajax({
            type: "post",
            url: linkOrderInformations,
            dataType: "json",
            data: {id_order: idOrder},
            success: function(data) {
                loader.toggle(false);
                console.log(data);
            },
            error: function(){
                loader.toggle(false);
            }
        });
    });

    // Input select number of echeance
    $('#nombreEcheances').change(function (evt) {
        numberEcheance = evt.target.value;
    });

    $('#accompte').change(function (evt) {
        accompte = formatNumber(evt.target.value);
        evt.target.value = accompte;
        setAccompte();
    });

    function setAccompte() {
        loader.toggle(true);
        $.ajax({
            type: "post",
            dataType: "json",
            url: linkSetAccompte,
            data: {
                id_order: idOrder,
                accompte: accompte
            },
            success: function (data) {
                console.log(data);
                loader.toggle(false);
            },
            error: function () {
                console.log("Error setAccompte");
                loader.toggle(false);
            }
        });
    }

    function getOrderInformations() {
        loader.toggle(true);
        $.ajax({
            type: "post",
            dataType: "json",
            url: linkOrderInformations,
            data: {
                id_order: idOrder,
            },
            success: function (data) {
                console.log(data);
                updateVariables(data);
                loader.toggle(false);
            },
            error: function () {
                console.log("Error getOrderInformations");
                loader.toggle(false);
            }
        });
    }

    function updateVariables(data) {
        orders_total_paid_tax_incl = data.orders_total_paid_tax_incl;
        total_paid = data.total_paid;
        order_reste_a_payer = data.order_reste_a_payer;
        paymentsNumber = data.paymentsNumber;
        numberEcheance = data.numberEcheance;
        accompte = data.accompte;

        updateDisplay();
    }

    function updateDisplay() {
        $("#totalAPayer").text(formatPrice(orders_total_paid_tax_incl));
        $("#totalDejaPaye").text(formatPrice(total_paid));
        $("#resteAPayer").text(formatPrice(order_reste_a_payer));
        // Todo mettre à jour le champ select du nombre d'accompte
    }

    function formatPrice(price) {
        return new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(price);
    }

    function formatNumber(number) {
        var result = parseFloat(Math.abs(number));
        return result.toFixed(2);
    }

    function initEcheancier() {
        getOrderInformations();
    }

});
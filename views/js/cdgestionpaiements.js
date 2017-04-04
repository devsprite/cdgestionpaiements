$(document).ready(function () {

    var total_paid_real;
    var orders_total_paid_tax_incl;
    var order_reste_a_payer;
    var paymentsNumber;
    var numberEcheance;
    var numberEcheanceMini;
    var accompte;
    var accompteMini = 20;
    var loader = $(".loader");
    var divErrors = $("#cdgestion-errors");
    var pErrors = $("#cdgestion-errors-message");
    var linkOrderInformations = adminGestionPaiementsController + "&action=GetOrderInformations&ajax=1";
    var linkUpdateAccompte = adminGestionPaiementsController + "&action=UpdateAccompte&ajax=1";
    var linkUpdateNbrEcheance = adminGestionPaiementsController + "&action=UpdateEcheance&ajax=1";
    var idOrder = id_order;

    initEcheancier();

    $("#getOrderInformation").click(function () {
        loader.toggle(true);
        $.ajax({
            type: "post",
            url: linkOrderInformations,
            dataType: "json",
            data: {id_order: idOrder},
            success: function (data) {
                loader.toggle(false);
                console.log(data);
            },
            error: function () {
                loader.toggle(false);
            }
        });
    });

    // Update nombre d'echeance //

    $('#nombreEcheances').change(function (evt) {
        numberEcheanceMin();
        numberEcheance = parseInt(evt.target.value);
        if (numberEcheance < numberEcheanceMini) {
            pErrors.text("Nombre d'échéance mini : " + numberEcheanceMini);
            divErrors.toggle(true);
        } else {
            updateNombreEcheance();
        }

    });

    function updateNombreEcheance() {
        loader.toggle(true);
        $.ajax({
            type: "post",
            url: linkUpdateNbrEcheance,
            dataType: "json",
            data: {
                id_order: idOrder,
                number_echeance: numberEcheance
            },
            success: function(data){
                console.log(data);
                loader.toggle(false);
                pErrors.text(data.message);
                divErrors.toggle(data.error);
            },
            error: function(data){
                console.log(data);
                loader.toggle(false);
            }
        });
    }

    function numberEcheanceMin() {
        // TODO Faire le controle du nombre d'echeance mini disponible
        numberEcheanceMini = 3;
        return numberEcheanceMini;
    }


    // Update Accompte //

    $('#accompte').change(function (evt) {
        accompte = formatNumber(evt.target.value);
        if (accompte < accompteMini) {
            accompte = 0;
            pErrors.text("Accompte Mini : " + accompteMini + " €");
            divErrors.toggle(true);
        } else {
            updateAccompte();
        }
        evt.target.value = accompte;
    });

    function updateAccompte() {
        loader.toggle(true);
        $.ajax({
            type: "post",
            dataType: "json",
            url: linkUpdateAccompte,
            data: {
                id_order: idOrder,
                accompte: accompte
            },
            success: function (data) {
                console.log(data);
                loader.toggle(false);
                pErrors.text(data.message);
                divErrors.toggle(data.error);
            },
            error: function (data) {
                console.log(data);
                loader.toggle(false);
            }
        });
    }

    /****************/


    function getOrderInformations() {
        loader.toggle(true);
        $.ajax({
            type: "post",
            dataType: "json",
            url: linkOrderInformations,
            data: {
                id_order: idOrder
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
        total_paid_real = data.total_paid_real;
        order_reste_a_payer = data.order_reste_a_payer;
        paymentsNumber = data.paymentsNumber;
        numberEcheance = data.numberEcheance;
        accompte = data.accompte;

        updateDisplay();
    }

    function updateDisplay() {
        $("#totalAPayer").text(formatPrice(orders_total_paid_tax_incl));
        $("#totalDejaPaye").text(formatPrice(total_paid_real));
        $("#resteAPayer").text(formatPrice(order_reste_a_payer));
        $("#accompte").val(accompte);
        $("#nombreEcheances").val(numberEcheance);
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
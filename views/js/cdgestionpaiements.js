$(document).ready(function () {

    var total_paid_real;
    var orders_total_paid_tax_incl;
    var order_reste_a_payer;
    var paymentsNumber;
    var numberEcheancesTotal;
    var numberEcheancesMini;
    var numberEcheancesMax;
    var accompte;
    var accompteMini = 20;
    var loader = $(".loader");
    var divErrors = $("#cdgestion-errors");
    var pErrors = $("#cdgestion-errors-message");

    var linkOrderInformations;
    var linkUpdateAccompte;
    var linkUpdateNbrEcheance;
    if (typeof adminGestionPaiementsController !== 'undefined') {
        linkOrderInformations = adminGestionPaiementsController + "&action=GetOrderInformations&ajax=1";
        linkUpdateAccompte = adminGestionPaiementsController + "&action=UpdateAccompte&ajax=1";
        linkUpdateNbrEcheance = adminGestionPaiementsController + "&action=UpdateEcheance&ajax=1";
    }

    var idOrder;
    if (typeof id_order !== 'undefined') {
        idOrder = id_order;
    }

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
        numberEcheancesTotal = parseInt(evt.target.value);
        if (numberEcheancesTotal < numberEcheancesMini) {
            pErrors.text("Nombre d'échéance mini : " + numberEcheancesMini);
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
                number_echeance: numberEcheancesTotal
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

    function numberEcheanceMin() {
        // TODO Faire le controle du nombre d'echeance mini disponible
        numberEcheancesMini = 3;
        return numberEcheancesMini;
    }


    // Update Accompte //

    $('#accompte').change(function (evt) {
        accompte = formatNumber(evt.target.value);
        console.log(accompte);
        if (accompte == "0.00") {
            updateAccompte();
        } else if ((accompte > order_reste_a_payer) || (accompte < accompteMini)) {
            pErrors.text("l'accompte doit être compris entre " + accompteMini + " € et " + order_reste_a_payer + " €");
            divErrors.toggle(true);
        } else {
            updateAccompte();
        }
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
        numberEcheancesTotal = data.numberEcheancesTotal;
        numberEcheancesMini = data.numberEcheancesMini;
        numberEcheancesMax = data.numberEcheancesMax;
        accompte = data.accompte;

        updateDisplay();
    }

    function updateDisplay() {
        $("#totalAPayer").text(formatPrice(orders_total_paid_tax_incl));
        $("#totalDejaPaye").text(formatPrice(total_paid_real));
        $("#resteAPayer").text(formatPrice(order_reste_a_payer));
        $("#accompte").val(accompte);
        initSelectNumberEcheance();
        initAccompte();
        displayInputs();
    }

    function initAccompte() {
        if (numberEcheancesMini >= 1) {
            $(".gestion-accompte").toggle(false);
        }
    }

    function initSelectNumberEcheance() {
        for (var i = numberEcheancesMini; i <= numberEcheancesMax; i++) {
            $("#nombreEcheances").append($('<option>', {
                value: i,
                text: i
            }));
        }
    }

    function displayInputs() {
        if (order_reste_a_payer <= 0) {
            $(".gestion-inputs").toggle(false);
        }
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
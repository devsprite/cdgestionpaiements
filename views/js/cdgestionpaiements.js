$(document).ready(function () {

    var total_paid_real = 0;
    var orders_total_paid_tax_incl = 0;
    var order_reste_a_payer = 0;
    var paymentsNumber = 0;
    var numberEcheancesTotal = 0;
    var numberEcheancesMini = 0;
    var numberEcheancesMax = 0;
    var accompte = 0;
    var accompteMini = 20;
    var loader = $(".loader");
    var divErrors = $("#cdgestion-errors");
    var pErrors = $("#cdgestion-errors-message");

    var linkOrderInformations = '';
    var linkUpdateAccompte = '';
    var linkUpdateNbrEcheance = '';
    if (typeof adminGestionPaiementsController !== 'undefined') {
        linkOrderInformations = adminGestionPaiementsController + "&action=GetOrderInformations&ajax=1";
        linkUpdateAccompte = adminGestionPaiementsController + "&action=UpdateAccompte&ajax=1";
        linkUpdateNbrEcheance = adminGestionPaiementsController + "&action=UpdateEcheance&ajax=1";
    }

    var idOrder = 0;
    if (typeof id_order !== 'undefined') {
        idOrder = id_order;
    }

    var echeancier = {echeancier:[]};
    var _echeancier = {
        echeancier:[
            {
                idEcheancier: 123,
                btnSubmitType: 'primary',
                btnSubmitName: 'submitAjouterEcheancier',
                btnSubmitText: 'Ajouter',
                paymentDate: '2017-03-01',
                paymentMethods: [{paymentMethod:'Carte bancaire'},{paymentMethod:'Virement'}],
                paymentTransactionId: 756981,
                checked:'success',
                paymentAmount: 53.68,
                invoices:[
                    {invoiceNumber:75023, invoiceFormated: '#FA075023'},
                    {invoiceNumber:75024, invoiceFormated: '#FA075024'}
                ]
            },
            {
                idEcheancier: 124,
                btnSubmitType: 'primary',
                btnSubmitName: 'submitAjouterEcheancier',
                btnSubmitText: 'Valider',
                paymentDate: '2017-04-01',
                paymentMethods: [{paymentMethod:'Carte bancaire'},{paymentMethod:'Virement'}],
                paymentTransactionId: 756352,
                checked:'danger',
                paymentAmount: 53.68,
                invoices:[
                    {invoiceNumber:75023, invoiceFormated: '#FA075023'}
                ]
            },
            {
                idEcheancier: 125,
                btnSubmitType: 'primary',
                btnSubmitName: 'submitAjouterEcheancier',
                btnSubmitText: 'Ajouter',
                paymentDate: '2017-05-01',
                paymentMethods: [{paymentMethod:'Carte bancaire'},{paymentMethod:'Virement'}],
                paymentTransactionId: '',
                checked:'',
                paymentAmount: 53.68,
                invoices:[
                    {invoiceNumber:75023, invoiceFormated: '#FA075023'}
                ]
            },
            {
                idEcheancier: 126,
                btnSubmitType: 'primary',
                btnSubmitName: 'submitAjouterEcheancier',
                btnSubmitText: 'Valider',
                paymentDate: '2017-06-01',
                paymentMethods: [{paymentMethod:'Carte bancaire'},{paymentMethod:'Virement'}],
                paymentTransactionId: '',
                checked:'',
                paymentAmount: 53.68,
                invoices:[
                    {invoiceNumber:75023, invoiceFormated: '#FA075023'}
                ]
            }
        ]
    };

    updateEcheancier();

    $("#getOrderInformation").click(function () {
        loader.show();
        getOrderInformations();
    });

    // Update nombre d'echeance //
    $('#nombreEcheances').change(function (evt) {
        numberEcheancesTotal = parseInt(evt.target.value);
        if (numberEcheancesTotal < numberEcheancesMini) {
            pErrors.text("Nombre d'échéance mini : " + numberEcheancesMini);
            divErrors.show();
        } else {
            updateNombreEcheance();
        }
    });

    function updateNombreEcheance() {
        loader.show();
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
                loader.hide();
                pErrors.text(data.message);
                divErrors.toggle(data.error);
                if (data.error == false) {
                    getOrderInformations();
                }
            },
            error: function (data) {
                console.log(data);
                loader.hide();
            }
        });
    }

    // Update Accompte //

    $('#accompte').change(function (evt) {
        accompte = formatNumber(evt.target.value);
        console.log(accompte);
        if (accompte == "0.00") {
            updateAccompte();
        } else if ((accompte > order_reste_a_payer) || (accompte < accompteMini)) {
            pErrors.text("l'accompte doit être compris entre " + accompteMini + " € et " + order_reste_a_payer + " €");
            divErrors.show();
        } else {
            updateAccompte();
        }
    });

    function updateAccompte() {
        loader.show();
        $.ajax({
            type: "post",
            dataType: "json",
            url: linkUpdateAccompte,
            data: {
                id_order: idOrder,
                number_echeance: numberEcheancesTotal,
                accompte: accompte
            },
            success: function (data) {
                console.log(data);
                loader.hide();
                pErrors.text(data.message);
                divErrors.toggle(data.error);
                if (data.error == false) {
                    getOrderInformations();
                }
            },
            error: function (data) {
                console.log(data);
                loader.hide();
            }
        });
    }
    /****************/


    function getOrderInformations() {
        loader.show();
        $.ajax({
            type: "post",
            dataType: "json",
            url: linkOrderInformations,
            data: {
                id_order: idOrder
            },
            success: function (data) {
                updateVariables(data);
                console.log(data);
                loader.hide();
            },
            error: function () {
                console.log("Error getOrderInformations");
                loader.hide();
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
        accompteMini = data.accompteMini;
        echeancier = {echeancier:data.echeancier};

        updateDisplay();
    }

    function updateDisplay() {
        $("#totalAPayer").text(formatPrice(orders_total_paid_tax_incl));
        $("#totalDejaPaye").text(formatPrice(total_paid_real));
        $("#resteAPayer").text(formatPrice(order_reste_a_payer));
        $("#accompte").val(accompte);
        updateSelectNumberEcheance();
        displayAccompte();
        displayInputs();
        displayEcheances();
    }

    function displayEcheances() {
        if (echeancier.echeancier.length > 0) {
            var rendered = Mustache.render(templatePayment, echeancier);
            $("#cdgestionEcheancier").html(rendered);
        }
    }

    function displayAccompte() {
        if (numberEcheancesMini >= 1) {
            $(".gestion-accompte").hide();
        }
    }

    function updateSelectNumberEcheance() {
        for (var i = numberEcheancesMini; i <= numberEcheancesMax; i++) {
            var selected = '';
            if (i == numberEcheancesTotal) {
                selected = 'selected';
            } else {
                selected = false;
            }

            $("#nombreEcheances").append($('<option>', {
                value: i,
                text: i,
                selected: selected
            }));
        }
    }

    // Cache les champs nombre d'echeances et accompte si il n'y a plus rien à payer
    function displayInputs() {
        if (order_reste_a_payer <= 0) {
            $(".gestion-inputs").hide();
        }
    }

    function formatPrice(price) {
        return new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(price);
    }

    function formatNumber(number) {
        var result = parseFloat(Math.abs(number));
        return result.toFixed(2);
    }

    function updateEcheancier() {
        getOrderInformations();
    }

});
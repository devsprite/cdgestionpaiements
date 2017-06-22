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
    var view = 0;
    var add = 0;
    var remove = 0;
    var edit = 0;
    var valid = 0;
    var profil;
    var echeancier = {echeancier:[]};
    var linkOrderInformations = '';
    var linkUpdateAccompte = '';
    var linkUpdateNbrEcheance = '';
    var linkUpdateInputEcheance = '';
    var linkGetProfil = '';
    if (typeof adminGestionPaiementsController !== 'undefined') {
        linkOrderInformations = adminGestionPaiementsController + "&action=GetOrderInformations&ajax=1";
        linkUpdateAccompte = adminGestionPaiementsController + "&action=UpdateAccompte&ajax=1";
        linkUpdateNbrEcheance = adminGestionPaiementsController + "&action=UpdateEcheance&ajax=1";
        linkUpdateInputEcheance = adminGestionPaiementsController + "&action=UpdateInputEcheance&ajax=1";
        linkGetProfil = adminGestionPaiementsController + "&action=GetProfil&ajax=1";
    }

    var idOrder = 0;
    if (typeof id_order !== 'undefined') {
        idOrder = id_order;
    }



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
    getProfil();

    function getProfil() {
        $.ajax({
            type: 'post',
            url: linkGetProfil,
            dataType: 'json',
            data: {},
            success: function(data) {
                view = data.view;
                edit = data.edit;
                remove = data.delete;
                add = data.add;
                if (view == 1) {
                    updateEcheancier();
                    $('#cdgestion').removeClass('hidden').show();
                }
            }
        });
    }

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
        console.log(orders_total_paid_tax_incl);
        dixPourcentTotal = formatNumber(orders_total_paid_tax_incl * 0.1);

        if(accompteMini < dixPourcentTotal) {
            accompteMini = dixPourcentTotal;
        }
        updateAccompte();
        // if (accompte == "0.00") {
        //     updateAccompte();
        // } else if ((accompte > order_reste_a_payer) || (accompte < accompteMini)) {
        //     pErrors.text("l'accompte doit être compris entre " + accompteMini + " € et " + order_reste_a_payer + " €");
        //     divErrors.show();
        // } else {
        //     updateAccompte();
        // }
    });

    function updateAccompte() {
        loader.show();
        accompte = accompte * 100;
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
        accompte = formatNumber(data.accompte);
        accompteMini = data.accompteMini;
        valid = data.valid;
        profil = data.profil;
        echeancier = {echeancier:data.echeancier};

        updateDisplay();
    }

    function updateEcheancier() {
        getOrderInformations();
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
            $("#gestionTBodyEcheances").click(function(evt) {
                if("button" == evt.target.type || "icon-trash" == evt.target.className || "icon-check" == evt.target.className) {
                    updateInput(evt);
                }
            });
            $('#gestionTBodyEcheances').change(function(evt){
                updateInput(evt);
            });
        } else {
            $("#cdgestionEcheancier").html('');
        }
        $(".datepicker").datepicker();
    }

    function updateInput(evt) {
        var inputEcheanceValues = {
            'id_order_gestion_echeancier': $(evt.target).data("echeance-id"),
            'input_name': evt.target.name,
            'data-name': $(evt.target).data("name"),
            'data-echeance-id' : $(evt.target).data("echeance-id"),
            'data-transaction-id' : $(evt.target).data("transaction-id"),
            'input_value': evt.target.value
        };
        console.log(inputEcheanceValues);
        updateInputEcheance(inputEcheanceValues);
    }

    function displayAccompte() {
        if (valid == 1 && profil.id_profile != 1) {
            $(".gestion-accompte").hide();
            $(".gestion-inputs").hide();
        } else {
            $(".gestion-accompte").show();
            $(".gestion-inputs").show();

        }
    }

    // Met à jour un champ de l'écheancier
    function updateInputEcheance(inputEcheanceValues) {
        loader.show();
        $.ajax({
            url: linkUpdateInputEcheance,
            type: "post",
            dataType: "json",
            data: {inputValues: inputEcheanceValues},
            success: function(data){
                getOrderInformations();
                console.log(data);
            },
            error: function(data){
                console.log("Error updateInputEcheance");
                loader.hide();
            }
        });
    }

    function updateSelectNumberEcheance() {
        $("#nombreEcheances > option").remove();
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

});
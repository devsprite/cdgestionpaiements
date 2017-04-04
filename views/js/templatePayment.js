var templatePayment = '' +
    '<div class="table-responsive">' +
    '<table class="table">' +
    '<thead>' +
    '   <tr>' +
    '       <th><span class="title_box ">Date</span></th>' +
    '       <th><span class="title_box ">Méthode de paiement</span></th>' +
    '       <th><span class="title_box ">ID de la transaction</span></th>' +
    '       <th><span class="title_box ">Montant</span></th>' +
    '       <th><span class="title_box ">Facture</span></th>' +
    '       <th></th>' +
    '   </tr>' +
    '</thead>' +
    '<tbody>' +
    '   <tr class="current-edit hidden-print">' +
    '       <td>' +
    '           <div class="input-group fixed-width-xl">' +
    '               <input type="text" name="payment_date" class="datepicker hasDatepicker" value="2017-04-04" id="dp1491312768749">' +
    '               <div class="input-group-addon">' +
    '                   <i class="icon-calendar-o"></i>' +
    '               </div>' +
    '           </div>' +
    '       </td>' +
    '       <td>' +
    '           <input name="payment_method" list="payment_method" class="payment_method">' +
    '           <datalist id="payment_method">' +
    '               <option value="Chèque"></option>' +
    '               <option value="Virement bancaire"></option>' +
    '               <option value="PayPal"></option>' +
    '               <option value="Carte Bancaire"></option>' +
    '               <option value="Gestion des abonnements"></option>' +
    '           </datalist>' +
    '       </td>' +
    '    <td>' + //TODO reprendre ici
    '    <input type="text" name="payment_transaction_id" value="" class="form-control fixed-width-sm">' +
    '    </td>' +
    '    <td>' +
    '    <input type="text" name="payment_amount" value="" class="form-control fixed-width-sm pull-left">' +
    '    <select name="payment_currency" class="payment_currency form-control fixed-width-xs pull-left">' +
    '    <option value="1" selected="selected">€</option>' +
    '    </select>' +
    '    </td>' +
    '    <td>' +
    '    <select name="payment_invoice" id="payment_invoice">' +
    '    <option value="60605" selected="selected">#FA060711</option>' +
    '</select>' +
    '</td>' +
    '<td class="actions">' +
    '    <button class="btn btn-primary btn-block" type="submit" name="submitAddPayment">' +
    '    Ajouter' +
    '    </button>' +
    '    </td>' +
    '    </tr>' +
    '' +
    '    </tbody>' +
    '    </table>' +
    '    </div>';

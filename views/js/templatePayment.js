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
    '{{#echeancier}}' +
    '   <tr class="current-edit hidden-print {{checked}}">' +
    '       <td>' +
    '           <div class="input-group fixed-width-xl">' +
    '               <input type="text" name="payment_date" class="datepicker" value="{{paymentDate}}" data-echeancier-id="{{idEcheancier}}">' +
    '               <div class="input-group-addon">' +
    '                   <i class="icon-calendar-o"></i>' +
    '               </div>' +
    '           </div>' +
    '       </td>' +
    '       <td>' +
    '           <input name="payment_method_{{idEcheancier}}" value="{{paymentMethod}}" list="payment_method_{{idEcheancier}}" class="payment_method" data-echeancier-id="{{idEcheancier}}">' +
    '           <datalist id="payment_method_{{idEcheancier}}">' +
    '{{#paymentMethods}}' +
    '               <option value="{{paymentMethod}}"></option>' +
    '{{/paymentMethods}}' +
    '           </datalist>' +
    '       </td>' +
    '       <td>' +
    '           <input type="text" name="payment_transaction_id" value="{{paymentTransactionId}}" class="form-control fixed-width-sm" data-echeancier-id="{{idEcheancier}}">' +
    '       </td>' +
    '       <td>' +
    '           <input type="text" name="payment_amount" value="{{paymentAmount}}" class="form-control fixed-width-sm pull-left" data-echeancier-id="{{idEcheancier}}">' +
    '           <select name="payment_currency" class="payment_currency form-control fixed-width-xs pull-left">' +
    '               <option value="1" selected="selected">€</option>' +
    '           </select>' +
    '       </td>' +
    '       <td>' +
    '           <select name="payment_invoice" id="" data-echeancier-id="{{idEcheancier}}">' +
    '{{#invoices}}' +
    '               <option value="{{invoiceNumber}}" selected="selected">{{invoiceFormated}}</option>' +
    '{{/invoices}}' +
    '           </select>' +
    '       </td>' +
    '       <td class="actions">' +
    '           <button class="btn btn-{{btnSubmitType}} btn-block" type="button" name="{{btnSubmitName}}" data-echeancier-id="{{idEcheancier}}">{{btnSubmitText}}</button>' +
    '       </td>' +
    '    </tr>' +
    '{{/echeancier}}' +
    '</tbody>' +
    '</table>' +
    '</div>';

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
    '<tbody id="gestionTBodyEcheances">' +
    '{{#echeancier}}' +
    '   <tr class="current-edit hidden-print {{checked}}">' +
    '       <td>' +
    '           <div class="input-group fixed-width-xl">' +
    '               <input type="text" name="payment_date" class="datepicker" value="{{paymentDate}}" data-echeance-id="{{idEcheancier}}">' +
    '               <div class="input-group-addon">' +
    '                   <i class="icon-calendar-o"></i>' +
    '               </div>' +
    '           </div>' +
    '       </td>' +
    '       <td>' +
    '           <input name="payment_method" value="{{paymentMethod}}" list="payment_method_{{idEcheancier}}" class="payment_method" data-echeance-id="{{idEcheancier}}">' +
    '           <datalist id="payment_method_{{idEcheancier}}">' +
    '{{#paymentMethods}}' +
    '               <option value="{{paymentMethod}}"></option>' +
    '{{/paymentMethods}}' +
    '           </datalist>' +
    '       </td>' +
    '       <td>' +
    '           <input type="text" name="payment_transaction_id" value="{{paymentTransactionId}}" class="form-control fixed-width-sm" data-echeance-id="{{idEcheancier}}">' +
    '       </td>' +
    '       <td>' +
    '           <div class="input-group col-xs-3">' +
    '               <input type="text" name="payment_amount" value="{{paymentAmount}}" class="form-control fixed-width-sm pull-left" data-echeance-id="{{idEcheancier}}">' +
    '               <div class="input-group-addon">€</div>' +
    '           </div>' +
    '       </td>' +
    '       <td>' +
    '           <select name="payment_invoice" id="" data-echeance-id="{{idEcheancier}}">' +
    '{{#invoices}}' +
    '               <option value="{{invoiceNumber}}" selected="selected">{{invoiceFormated}}</option>' +
    '{{/invoices}}' +
    '           </select>' +
    '       </td>' +
    '       <td class="actions">' +
    '           <button class="btn btn-{{btnSubmitClass}} btn-block" type="button" name="{{btnSubmitName}}" data-echeance-id="{{idEcheancier}}">{{btnSubmitText}}</button>' +
    '       </td>' +
    '    </tr>' +
    '{{/echeancier}}' +
    '</tbody>' +
    '</table>' +
    '</div>';

$(document).ready(function()
{
    $("#fileuploader").uploadFile({
        url:linkUpload,
        fileName:"csvPaybox",
        allowedTypes:"zip",
        redirect:"localhost",
        uploadStr:"Uploader votre fichier Paybox",
        dragDropStr: "<span><b>Glisser déposer</b></span>",
        abortStr: "Annuler",
        doneStr: "Traitement terminé",
        add: function (e, data) {
            data.context = $('<button/>').text('Upload')
                .appendTo(document.body)
                .click(function () {
                    data.context = $('<p/>').text('Uploading...').replaceAll($(this));
                    data.submit();
                });
        },
        done: function (e, data) {
            console.log('upload done');
        }

    });
});


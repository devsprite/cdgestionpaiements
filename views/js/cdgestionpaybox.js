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

    });
});
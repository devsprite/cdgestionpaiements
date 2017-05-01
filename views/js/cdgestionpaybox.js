$(document).ready(function()
{
    $("#fileuploader").uploadFile({
        url:linkUpload,
        fileName:"csvPaybox",
        allowedTypes:"zip"
    });

    $(".pay_ok").closest("td").css("background-color", "#8bc954");
    $(".pay_not_ok").closest("td").css("background-color", "#f96060");
});
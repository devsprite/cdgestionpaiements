{if $employee_profile['add'] == 1}
<script>
    var linkUpload = "{$link->getAdminLink('AdminGestionPaybox')|addslashes}&action=uploadCsv&ajax=1";
</script>
<div class="row">
    <div class="col-xs-6">
        <div class="panel">
            <div class="panel-heading">
                <h4>Upload csv Paybox</h4>
            </div>
            <div class="panel-body">
                <div id="fileuploader"></div>
            </div>
        </div>
    </div>
</div>
{/if}
<?php
/**
 * @var string $controller_name
 * @var string $table_headers
 * @var array $config
 */
?>

<?= view('partial/header') ?>

<script type="text/javascript">
    $(document).ready(function() {
        <?= view('partial/bootstrap_tables_locale') ?>

        table_support.init({
            resource: '<?= esc($controller_name) ?>',
            headers: <?= $table_headers ?>,
            pageSize: <?= $config['lines_per_page'] ?>,
            uniqueId: 'people.person_id',
            enableActions: function() {
                var email_disabled = $("td input:checkbox:checked").parents("tr").find("td a[href^='mailto:']").length == 0;
                $("#email").prop('disabled', email_disabled);
            }
        });

        $("#email").click(function(event) {
            var recipients = $.map($("tr.selected a[href^='mailto:']"), function(element) {
                return $(element).attr('href').replace(/^mailto:/, '');
            });
            location.href = "mailto:" + recipients.join(",");
        });

        $("#send_message").click(function(event) {
            var selected_ids = table_support.selected_ids();
            if (selected_ids.length > 0) {
                var form = $('<form>', {
                    'action': '<?= site_url(esc($controller_name) . "/bulk_message") ?>',
                    'method': 'POST'
                }).append($('<input>', {
                    'type': 'hidden',
                    'name': '<?= csrf_token() ?>',
                    'value': '<?= csrf_hash() ?>'
                })).append($('<input>', {
                    'type': 'hidden',
                    'name': 'customer_ids',
                    'value': selected_ids.join(',')
                }));
                $(document.body).append(form);
                form.submit();
            } else {
                $.notify("Please select at least one person.", { type: 'warning' });
            }
        });
    });
</script>

<div id="title_bar" class="btn-toolbar">
    <?php if ($controller_name === 'customers') { ?>
        <button class="btn btn-info btn-sm pull-right modal-dlg" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= "$controller_name/csvImport" ?>" title="<?= lang(ucfirst($controller_name) . '.import_items_csv') ?>">
            <span class="glyphicon glyphicon-import">&nbsp;</span><?= lang('Common.import_csv') ?>
        </button>
    <?php } ?>
    <button class="btn btn-info btn-sm pull-right modal-dlg" data-btn-submit="<?= lang('Common.submit') ?>" data-href="<?= "$controller_name/view" ?>" title="<?= lang(ucfirst($controller_name) . '.new') ?>">
        <span class="glyphicon glyphicon-user">&nbsp;</span><?= lang(ucfirst($controller_name) . '.new') ?>
    </button>
</div>

<div id="toolbar">
    <div class="pull-left btn-toolbar">
        <button id="delete" class="btn btn-default btn-sm">
            <span class="glyphicon glyphicon-trash">&nbsp;</span><?= lang('Common.delete') ?>
        </button>
        <button id="email" class="btn btn-default btn-sm">
            <span class="glyphicon glyphicon-envelope">&nbsp;</span><?= lang('Common.email') ?>
        </button>
        <button id="send_message" class="btn btn-default btn-sm">
            <span class="glyphicon glyphicon-send">&nbsp;</span>Send Message
        </button>
    </div>
</div>

<div id="table_holder">
    <table id="table"></table>
</div>

<?= view('partial/footer') ?>

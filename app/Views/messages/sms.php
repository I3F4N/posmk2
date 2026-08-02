<?= view('partial/header') ?>

<script type="text/javascript">
    dialog_support.init("a.modal-dlg");
</script>

<div class="jumbotron" style="max-width: 60%; margin: auto;">
    <?= form_open("messages/send/", ['id' => 'send_sms_form', 'enctype' => 'multipart/form-data', 'method' => 'post', 'class' => 'form-horizontal']) ?>
        <fieldset>

            <legend style="text-align: center;"><?= lang('Messages.sms_send') ?></legend>
            <div class="form-group form-group-sm">
                <label for="message_type" class="col-xs-3 control-label">Message Type</label>
                <div class="col-xs-9">
                    <select name="message_type" id="message_type" class="form-control input-sm">
                        <option value="sms">SMS</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                    </select>
                </div>
            </div>

            <div class="form-group form-group-sm">
            <div class="form-group form-group-sm">
                <label for="phone" class="col-xs-3 control-label">Recipient(s)</label>
                <div class="col-xs-9">
                    <input class="form-control input-sm" type="text" name="phone" id="phone" value="<?= esc($phone ?? '') ?>" placeholder="Comma-separated phones or emails">
                    <span class="help-block" style="text-align: center;"><?= lang('Messages.multiple_phones') ?></span>
                </div>
            </div>

            <div class="form-group form-group-sm" id="subject_group" style="display: none;">
                <label for="subject" class="col-xs-3 control-label">Subject</label>
                <div class="col-xs-9">
                    <input class="form-control input-sm" type="text" name="subject" placeholder="Subject (Required for Email)">
                </div>
            </div>

            <div class="form-group form-group-sm">
                <label for="message" class="col-xs-3 control-label"><?= lang('Messages.message') ?></label>
                <div class="col-xs-9">
                    <textarea class="form-control input-sm" rows="3" id="message" name="message" placeholder="<?= lang('Messages.message_placeholder') ?>"></textarea>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <label for="attachment" class="col-xs-3 control-label">Attachment (Optional)</label>
                <div class="col-xs-9">
                    <input type="file" name="attachment" id="attachment" class="form-control input-sm" accept="image/*,application/pdf">
                </div>
            </div>

            <?= form_submit([
                'name'  => 'submit_form',
                'id'    => 'submit_form',
                'value' => lang('Common.submit'),
                'class' => 'btn btn-primary btn-sm pull-right'
            ]) ?>

        </fieldset>
    <?= form_close() ?>
</div>

<?= view('partial/footer') ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#send_sms_form').validate({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        $.notify({
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        })
                    },
                    dataType: 'json'
                });
            }
        });

        $('#message_type').change(function() {
            if ($(this).val() === 'email') {
                $('#subject_group').show();
            } else {
                $('#subject_group').hide();
            }
        }).trigger('change');
    });
</script>

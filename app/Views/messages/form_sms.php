<?php
/**
 * @var object $person_info
 * @var string $controller_name
 * @var array $config
 */
?>

<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
<ul id="error_message_box" class="error_message_box"></ul>

<?= form_open("messages/send_form/$person_info->person_id", ['id' => 'send_sms_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>
    <fieldset>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Messages.first_name'), 'first_name_label', ['for' => 'first_name', 'class' => 'control-label col-xs-2']) ?>
            <div class="col-xs-10">
                <?= form_input(['class' => 'form-control input-sm', 'type' => 'text', 'name' => 'first_name', 'value' => $person_info->first_name, 'readonly' => 'true']) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Messages.last_name'), 'last_name_label', ['for' => 'last_name', 'class' => 'control-label col-xs-2']) ?>
            <div class="col-xs-10">
                <?= form_input(['class' => 'form-control input-sm', 'type' => 'text', 'name' => 'last_name', 'value' => $person_info->last_name, 'readonly' => 'true']) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <label for="message_type" class="control-label col-xs-2">Message Type</label>
            <div class="col-xs-10">
                <select name="message_type" id="message_type" class="form-control input-sm">
                    <option value="sms">SMS</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="email">Email</option>
                </select>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <label for="phone" class="control-label col-xs-2 required">Recipient</label>
            <div class="col-xs-10">
                <div class="input-group">
                    <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-send"></span></span>
                    <input class="form-control input-sm required" type="text" name="phone" id="phone" value="<?= esc($person_info->phone_number) ?>" data-phone="<?= esc($person_info->phone_number) ?>" data-email="<?= esc($person_info->email ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="form-group form-group-sm" id="subject_group" style="display: none;">
            <label for="subject" class="control-label col-xs-2">Subject</label>
            <div class="col-xs-10">
                <input class="form-control input-sm" type="text" name="subject" placeholder="Subject (Required for Email)">
            </div>
        </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Messages.message'), 'message_label', ['for' => 'message', 'class' => 'control-label col-xs-2 required']) ?>
                <div class="col-xs-10">
                    <?= form_textarea(['class' => 'form-control input-sm required', 'name' => 'message', 'id' => 'message', 'value' => $config['msg_msg']]) ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <label for="attachment" class="control-label col-xs-2">Attachment (Optional)</label>
                <div class="col-xs-10">
                    <input type="file" name="attachment" id="attachment" class="form-control input-sm" accept="image/*,application/pdf">
                </div>
            </div>

    </fieldset>
<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('#send_sms_form').validate($.extend({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        dialog_support.hide();
                        table_support.handle_submit("<?= esc($controller_name) ?>", response);
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#error_message_box',

            rules: {
                phone: {
                    required: true
                },
                message: {
                    required: true,
                    number: false
                }
            },

            messages: {
                phone: {
                    required: "<?= lang('Messages.phone_number_required') ?>",
                    number: "<?= lang('Messages.phone') ?>"
                },
                message: {
                    required: "<?= lang('Messages.message_required') ?>"
                }
            }
        }, form_support.error));

        $('#message_type').change(function() {
            var input = $('#phone');
            if ($(this).val() === 'email') {
                $('#subject_group').show();
                input.val(input.data('email'));
            } else {
                $('#subject_group').hide();
                input.val(input.data('phone'));
            }
        }).trigger('change');
    });
</script>

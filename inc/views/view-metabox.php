<div id="postp-metabox-wrap">
    <?php if (!isset($api_status) || !isset($api_status->data) || !$api_status) : ?>
        <p>Please check your PostPlay <a href="<?php echo admin_url('options-general.php?page=postplay-options'); ?>">API settings</a>.</p>
    <?php else: ?>
	<div id="credit-bal">You have <?php echo $api_status->data->credits; ?> credits available</div>
    <p>Would you like this post in audio format as well?</p>
    <div id="credit-charge-wrap">
        <div id="charge-display-oval">0</div>
    </div>
    <h1 id="credits-spend">Credits</h1>
    <h4 id="pp-words-count"><span class='count-dsp'>0</span> Words</h4>

    <div id="postp-switch-wrap">
        <div id="postp-switch">
            <div class="switch-split switch-yes">Yes</div>
            <div class="switch-split switch-no active">No</div>
            <input type="hidden" name="postplay_send" id="postplay_send" value="0">
        </div>
    </div>
	<?php endif; ?>
</div>

<script>
    function toggleSendValue() {
        var theVal = jQuery("#postplay_send").val();
        if (theVal != '1') {
            jQuery("#postplay_send").val('1');
        } else {
            jQuery("#postplay_send").val('0');
        }

    }
    jQuery(document).on('click', '.switch-split', function () {
        jQuery('.switch-split').toggleClass('active');
        toggleSendValue();
    });

    function postplayWordCount(str) {
        return str.split(" ").length;
    }

    function countContentLength() {
        var textContent = tinymce.editors.content.getBody().textContent;
        var theCount = postplayWordCount(textContent);
        jQuery("#pp-words-count span.count-dsp").html(theCount);
        jQuery('#charge-display-oval').html(Math.ceil((theCount / 1000)));
    }


    jQuery(document).on('ready', function () {
        setTimeout(function () {
            countContentLength();
            tinymce.editors.content.on('change', function (e) {
                countContentLength();
            });
        }, 1000);

    });

</script>
<?php
/**
 * @brief Magalogue, a theme for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Themes
 *
 * @copyright Noé Cendrier & Association Dotclear
 * @copyright GPL-2.0-only
 */

namespace themes\magalogue;

if (!defined('DC_RC_PATH')) {return;}
// public part below

if (!defined('DC_CONTEXT_ADMIN')) {return false;}
// admin part below

# Behaviors
$GLOBALS['core']->addBehavior('adminPageHTMLHead', [__NAMESPACE__ . '\tplMagalogueThemeAdmin', 'adminPageHTMLHead']);

class tplMagalogueThemeAdmin
{
    public static function adminPageHTMLHead()
    {
        global $core;
        if ($core->blog->settings->system->theme != 'magalogue') {return;}

        echo "\n" . '<!-- Header directives for Magalogue configuration -->' . "\n";
        $core->auth->user_prefs->addWorkspace('accessibility');
        if (!$core->auth->user_prefs->accessibility->nodragdrop) {
            echo
            \dcPage::jsLoad('js/jquery/jquery-ui.custom.js') .
            \dcPage::jsLoad('js/jquery/jquery.ui.touch-punch.js');
            echo <<<EOT
<script>
$(function() {
    $('#stickerslist').sortable({'cursor':'move'});
    $('#stickerslist tr').hover(function () {
        $(this).css({'cursor':'move'});
    }, function () {
        $(this).css({'cursor':'auto'});
    });
    $('#theme_config').submit(function() {
        var order=[];
        $('#stickerslist tr td input.position').each(function() {
            order.push(this.name.replace(/^order\[([^\]]+)\]$/,'$1'));
        });
        $('input[name=ds_order]')[0].value = order.join(',');
        return true;
    });
    $('#stickerslist tr td input.position').hide();
    $('#stickerslist tr td.handle').addClass('handler');
});
</script>
<style>
.linkimg img {
    padding: 3px;
    background-color: #fff;
}
</style>
EOT;
        }

    }
}

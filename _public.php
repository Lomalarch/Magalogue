<?php
# -- BEGIN LICENSE BLOCK ---------------------------------------
# This file is part of Magalogue,
# a theme for Dotclear
#
# Copyright (c) Noé Cendrier
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK -----------------------------------------
namespace themes\magalogue;

if (!defined('DC_RC_PATH')) { return; }

\l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');
//__('Show menu').__('Hide menu').__('Navigation');

# Behaviors
$core->addBehavior('publicHeadContent',[__NAMESPACE__ . '\behaviorMagalogueTheme','publicHeadContent']);
// $core->addBehavior('publicEntryAfterContent',[__NAMESPACE__ . '\behaviorMagalogueTheme','publicEntryAfterContent']);

# Templates
$core->tpl->addValue('magalogueEntriesList', [__NAMESPACE__ . '\tplMagalogueTheme', 'magalogueEntriesList']);
$core->tpl->addValue('magalogueSliderContent', [__NAMESPACE__ . '\tplMagalogueTheme', 'magalogueSliderContent']);
$core->tpl->addValue('magalogueNbEntryOnHome', [__NAMESPACE__ . '\tplMagalogueTheme', 'magalogueNbEntryOnHome']);
$core->tpl->addValue('magalogueSocialLinks', [__NAMESPACE__ . '\tplMagalogueTheme', 'magalogueSocialLinks']);
//$core->tpl->addValue('magalogueNbEntryPerPage', [__NAMESPACE__ . '\tplMagalogueTheme', 'magalogueNbEntryPerPage']);
$core->tpl->addValue('magalogueLogoSrc', [__NAMESPACE__ . '\tplMagalogueTheme', 'magalogueLogoSrc']);
//$core->tpl->addValue('magalogueRelatedEntries', [__NAMESPACE__ . '\tplMagalogueTheme', 'magalogueRelatedEntries']);
$core->tpl->addBlock('magalogueRelatedEntries', [__NAMESPACE__ . '\tplMagalogueTheme', 'magalogueRelatedEntries']);
//$core->tpl->addBlock('IfPreviewIsNotMandatory', [__NAMESPACE__ . '\tplMagalogueTheme', 'IfPreviewIsNotMandatory']);



class behaviorMagalogueTheme
{
    public static function publicHeadContent()
    {
        echo \dcUtils::jsVars(array(
            'dotclear_magalogue_show_menu' => __('Show menu'),
            'dotclear_magalogue_hide_menu' => __('Hide menu'),
            'dotclear_magalogue_navigation' => __('Navigation')
            ));
    }
}

class tplMagalogueTheme
{
    public static function magalogueEntriesList($attr)
    {
        #Entries in home main block
        global $core;

        $tpl_path   = dirname(__FILE__) . '/tpl/';
        $entries_list_types = ['selected', 'first-level-categories', 'categories', 'recent'];

        // Get all _home-entries-*.html in tpl folder of theme
        $list_types_templates = \files::scandir($tpl_path);
        if (is_array($list_types_templates)) {
            foreach ($list_types_templates as $v) {
                if (preg_match('/^_home\-entries\-(.*)\.html$/', $v, $m)) {
                    if (isset($m[1])) {
                        if (!in_array($m[1], $entries_list_types)) {
                            // template not already in full list
                            $entries_list_types[] = $m[1];
                        }
                    }
                }
            }
        }

        $default = isset($attr['default']) ? trim($attr['default']) : 'first-level-categories';
        $ret     = '<?php ' . "\n" .
        'switch (' . __NAMESPACE__ . '\tplMagalogueTheme::magalogueEntriesListHelper(\'list-' . $default . '\')) {' . "\n";

        foreach ($entries_list_types as $v) {
            $ret .= '   case \'' . $v . '\':' . "\n" .
            '?>' . "\n" .
            $core->tpl->includeFile(['src' => '_home-entries-' . $v . '.html']) . "\n" .
                '<?php ' . "\n" .
                '       break;' . "\n";
        }

        $ret .= '}' . "\n" .
            '?>';
        return $ret;
    }
    public static function magalogueSliderContent($attr) 
    {
        #Entries in home slider
        global $core;

        $tpl_path   = dirname(__FILE__) . '/tpl/';
        $slider_list_types = ['selected', 'first-level-categories', 'categories', 'recent'];

        // Get all _home-slider-*.html in tpl folder of theme
        $list_types_templates = \files::scandir($tpl_path);
        if (is_array($list_types_templates)) {
            foreach ($list_types_templates as $v) {
                if (preg_match('/^_home\-slider\-(.*)\.html$/', $v, $m)) {
                    if (isset($m[1])) {
                        if (!in_array($m[1], $slider_list_types)) {
                            // template not already in full list
                            $slider_list_types[] = $m[1];
                        }
                    }
                }
            }
        }

        $default = isset($attr['default']) ? trim($attr['default']) : 'selected';
        $ret     = '<?php ' . "\n" .
        'switch (' . __NAMESPACE__ . '\tplMagalogueTheme::magalogueEntriesListHelper(\'slider-' . $default . '\')) {' . "\n";

        foreach ($slider_list_types as $v) {
            $ret .= '   case \'' . $v . '\':' . "\n" .
            '?>' . "\n" .
            $core->tpl->includeFile(['src' => '_home-slider-' . $v . '.html']) . "\n" .
                '<?php ' . "\n" .
                '       break;' . "\n";
        }

        $ret .= '}' . "\n" .
            '?>';
        return $ret;
    }
    public static function magalogueEntriesListHelper($default)
    {
        $s = $GLOBALS['core']->blog->settings->themes->get($GLOBALS['core']->blog->settings->system->theme . '_entries_lists');
        if ($s !== null) {
            $s = @unserialize($s);
            if (is_array($s)) {
                if (isset($s['slider']) && preg_match('/^slider-/', $default)) {
                    $model = $s['slider'];
                    return $model;
                }
                elseif (isset($s['list']) && preg_match('/^list-/', $default)) {
                    $model = $s['list'];
                    return $model;
                }
            }
        }
        $default = preg_replace('/^(slider-|list-)/', '', $default);
        return $default;
    }

    public static function magalogueNbEntryOnHome($attr)
    {
        return '<?php ' . __NAMESPACE__ . '\tplMagalogueTheme::magalogueNbEntryOnHomeHelper(); ?>';
    }

    public static function magalogueNbEntryOnHomeHelper()
    {
        global $_ctx;

        $nb_other = $nb_first = 0;

        $s = $GLOBALS['core']->blog->settings->themes->get($GLOBALS['core']->blog->settings->system->theme . '_entries_counts');
        if ($s !== null) {
            $s = @unserialize($s);
            if (is_array($s)) {
                switch ($GLOBALS['core']->url->type) {
                    case 'default':
                    case 'default-page':
                        if (isset($s['slider'])) {
                            $nb_first = $nb_other = (integer) $s['slider'];
                        }
                        if (isset($s['list'])) {
                            $nb_other = (integer) $s['list'];
                        }
                        break;
                    default:
                        if (isset($s[$GLOBALS['core']->url->type])) {
                            // Nb de billets par page défini par la config du thème
                            $nb_first = $nb_other = (integer) $s[$GLOBALS['core']->url->type];
                        }
                        break;
                }
            }
        }

        if ($nb_other == 0) {
            if (!empty($attr['nb'])) {
                // Nb de billets par page défini par défaut dans le template
                $nb_other = $nb_first = (integer) $attr['nb'];
            }
        }

        if ($nb_other > 0) {
            $_ctx->nb_entry_per_page = $nb_other;
        }
        if ($nb_first > 0) {
            $_ctx->nb_entry_first_page = $nb_first;
        }
    }
    public static function magalogueSocialLinks($attr)
    {
        global $core;
        # Social media links
        $res     = '';
        $default = false;
        $img_url = $core->blog->settings->system->themes_url . '/' . $core->blog->settings->system->theme . '/img/';

        $s = $core->blog->settings->themes->get($core->blog->settings->system->theme . '_stickers');

        if ($s === null) {
            $default = true;
        } else {
            $s = @unserialize($s);
            if (!is_array($s)) {
                $default = true;
            } else {
                $s = array_filter($s, 'self::cleanSocialLinks');
                if (count($s) == 0) {
                    $default = true;
                } else {
                    $count = 1;
                    foreach ($s as $sticker) {
                        $res .= self::setSocialLink($count, ($count == count($s)), $sticker['label'], $sticker['url'], $img_url . $sticker['image']);
                        $count++;
                    }
                }
            }
        }

        if ($default || $res == '') {
            $res = self::setSocialLink(1, true, __('Subscribe'), $core->blog->url .
                $core->url->getURLFor('feed', 'atom'), $img_url . 'rss-link.png');
        }

        if ($res != '') {
            $res = '<ul id="stickers">' . "\n" . $res . '</ul>' . "\n";
            // echo $res;
            return $res;
        }
    }
    protected static function setSocialLink($position, $last, $label, $url, $image)
    {
        return '<li id="slink' . $position . '"' . ($last ? ' class="last"' : '') . '>' . "\n" .
            '<a href="' . $url . '">' . "\n" .
            '<img alt="' . $label . '" src="' . $image . '" title="' . $label . '" />' . "\n" .
            // '<span>' . $label . '</span>' . "\n" .
            '</a>' . "\n" .
            '</li>' . "\n";
    }

    protected static function cleanSocialLinks($s)
    {
        if (is_array($s)) {
            if (isset($s['label']) && isset($s['url']) && isset($s['image'])) {
                if ($s['label'] != null && $s['url'] != null && $s['image'] != null) {
                    return true;
                }
            }
        }
        return false;
    }
public static function magalogueLogoSrc($attr)
    {
        return '<?php echo ' . __NAMESPACE__ . '\tplMagalogueTheme::magalogueLogoSrcHelper(); ?>';
    }

    public static function magalogueLogoSrcHelper()
    {
        $img_url = $GLOBALS['core']->blog->settings->system->themes_url . '/' . $GLOBALS['core']->blog->settings->system->theme . '/img/MagalogueBanner.png';

        $s = $GLOBALS['core']->blog->settings->themes->get($GLOBALS['core']->blog->settings->system->theme . '_style');
        if ($s === null) {
            // no settings yet, return default logo
            return $img_url;
        }
        $s = @unserialize($s);
        if (!is_array($s)) {
            // settings error, return default logo
            return $img_url;
        }

        if (isset($s['logo_src'])) {
            if ($s['logo_src'] !== null) {
                if ($s['logo_src'] != '') {
                    if ((substr($s['logo_src'], 0, 1) == '/') || (parse_url($s['logo_src'], PHP_URL_SCHEME) != '')) {
                        // absolute URL
                        $img_url = $s['logo_src'];
                    } else {
                        // relative URL (base = img folder of magalogue theme)
                        $img_url = $GLOBALS['core']->blog->settings->system->themes_url . '/' . $GLOBALS['core']->blog->settings->system->theme . '/img/' . $s['logo_src'];
                    }
                }
            }
        }

        return $img_url;
    }

    public static function thisPostrelatedEntries ($id)
    {
        global $core;
        $meta = &$core->meta;
        $params['post_id'] = $id;
        $params['no_content'] = false;
        $params['post_type'] = array('post');

        $rs = $core->blog->getPosts($params);
        return $meta->getMetaStr($rs->post_meta,'relatedEntries');
    }

    public static function magalogueRelatedEntries($attr, $content)
    {
        global $core, $_ctx;
        # Settings

        if ($core->plugins->moduleExists('relatedEntries')) {
            if ($core->url->type == 'post')
            {

                $s = &$core->blog->settings->relatedEntries;

                if (!$s->relatedEntries_enabled) {
                    return;
                }

                $lastn = -1;
                if (isset($attr['lastn'])) {
                    $lastn = abs((integer) $attr['lastn']) + 0;
                }

                $rel = "if (" . __NAMESPACE__ . "\\tplMagalogueTheme::thisPostrelatedEntries(\$_ctx->posts->post_id) !== '') :\n";
                $rel .= "\$meta = &\$GLOBALS['core']->meta;\n";
                $rel .= "\$r_ids = " . __NAMESPACE__ . "\\tplMagalogueTheme::thisPostrelatedEntries(\$_ctx->posts->post_id);";

                $p = "\$params['post_id'] = \$meta->splitMetaValues(\$r_ids);\n";

                if ($lastn != 0) {
                    // Set limit (aka nb of entries needed)
                    if ($lastn > 0) {
                        // nb of entries per page specified in template -> regular pagination
                        $p .= "\$params['limit'] = " . $lastn . ";\n";
                        $p .= "\$nb_entry_first_page = \$nb_entry_per_page = " . $lastn . ";\n";
                    } else {
                        // nb of entries per page not specified -> use ctx settings
                        $p .= "\$nb_entry_first_page=\$_ctx->nb_entry_first_page; \$nb_entry_per_page = \$_ctx->nb_entry_per_page;\n";
                        $p .= "if ((\$core->url->type == 'default') || (\$core->url->type == 'default-page')) {\n";
                        $p .= "    \$params['limit'] = (\$_page_number == 1 ? \$nb_entry_first_page : \$nb_entry_per_page);\n";
                        $p .= "} else {\n";
                        $p .= "    \$params['limit'] = \$nb_entry_per_page;\n";
                        $p .= "}\n";
                    }
                    // no pagination, get all posts from 0 to limit
                    $p .= "\$params['limit'] = [0, \$params['limit']];\n";
                }

                if (isset($attr['no_content']) && $attr['no_content']) {
                    $p .= "\$params['no_content'] = true;\n";
                }

                $res = "<?php\n";
                $res .= $rel;
                $res .= $p;
                $res .= $core->callBehavior("templatePrepareParams",
                    ["tag" => "Entries", "method" => "blog::getPosts"],
                    $attr, $content);
                $res .= '$_ctx->post_params = $params;' . "\n";
                $res .= '$_ctx->posts = $core->blog->getPosts($params); unset($params);' . "\n";
                $res .= "?>\n";
                $res .=
                    '<?php while ($_ctx->posts->fetch()) : ?>' . $content . '<?php endwhile; ' .
                    '$_ctx->posts = null; $_ctx->post_params = null;
                    endif; ?>';

                return $res;
            }
        }
    }
}


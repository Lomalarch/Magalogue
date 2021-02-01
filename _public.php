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
$core->tpl->addBlock('magalogueSliderContent', [__NAMESPACE__ . '\tplMagalogueTheme', 'magalogueSliderContent']);
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
            'dotclear_berlin_show_menu' => __('Show menu'),
            'dotclear_berlin_hide_menu' => __('Hide menu'),
            'dotclear_berlin_navigation' => __('Navigation')
            ));
    }
    /* public static function thisPostrelatedEntries ($id)
    {
        global $core;
        $meta =& $core->meta;
        $params['post_id'] = $id;
        $params['no_content'] = false;
        $params['post_type'] = array('post');

        $rs = $core->blog->getPosts($params);
        return $meta->getMetaStr($rs->post_meta,'relatedEntries');
    }

    public static function publicEntryAfterContent($core,$_ctx)
    {
        global $core;
        # Settings

        if ($core->plugins->moduleExists('relatedEntries')) {
            if ($core->url->type == 'post')
            {

                $s = &$core->blog->settings->relatedEntries;

                if (!$s->relatedEntries_enabled) {
                    return;
                }
                // if (!$s->relatedEntries_afterPost) {
                //  return;
                // }
                if (self::thisPostrelatedEntries($_ctx->posts->post_id) != '') {

                    //related entries
                    $meta = &$GLOBALS['core']->meta;

                    $r_ids = self::thisPostrelatedEntries($_ctx->posts->post_id);
                    $params['post_id'] = $meta->splitMetaValues($r_ids);
                    $rs = $core->blog->getPosts($params);
                    $ret = '<h3 class="related-entries"><span>'.$s->relatedEntries_title.'</span></h3>'."\n";

                    $count = 0;

                    while ($count < 4 && $rs->fetch()) {
                        $ret .= '<div class="related-entry '.($count & 1 ? "" : "odd").'">'."\n";
                        if ($_ctx->posts->countMedia('featured')) {

                            if ($_ctx->posts !== null && $core->media) {
                                $_ctx->featured = new ArrayObject($core->media->getPostMedia($rs->post_id,null,"featured"));
                                foreach ($_ctx->featured as $featured_i => $featured_f) {
                                    // $GLOBALS['featured_i'] = $featured_i; $GLOBALS['featured_f'] = $featured_f;
                                    // $_ctx->file_url = $featured_f->file_url;
                                    if ($featured_f->media_image) {
                                        if (isset($featured_f->media_thumb['m']))
                                        {
                                            $img_url = $featured_f->media_thumb['m'];
                                        } else
                                        {
                                            $img_url = $featured_f->file_url;
                                        }
                                        $img_alt = $featured_f->media_title;

                                        // $ret .= '<div class="related-entry '.($count & 1 ? "" : "odd").'">'."\n";
                                        $ret .= '<div class="featured-media">'."\n";
                                        $ret .= '<img src="'.$img_url.'" alt="'.$img_alt.'" /></div>'."\n";
                                        //  $ret .= '<div class="related-contents">'."\n";
                                        //  $ret .= '<p class="related-category"><a href="'.$rs->getCategoryURL().'">'.$rs->cat_title.'</a></p>'."\n";
                                        //  $ret .= '<p class="related-title"><a href="'.$rs->getURL().'">'.$rs->post_title.'</a></p>'."\n";
                                        //  $ret .= '<p class="post-read-it"><a href="'.$rs->getURL().'" title="Lire '.$rs->post_title.'"><img src="'.$core->blog->settings->system->themes_url.'/'.$core->blog->settings->system->theme.'/img/'.$core->blog->id.'/read-it.png" alt="Lire la suite" /></a></p>'."\n";
                                        //  $ret .= '</div>';
                                    }
                                }
                                // $_ctx->featured = null; unset($featured_i,$featured_f,$_ctx->featured_url);

                            }
                        }
                        if (!$_ctx->posts->countMedia('featured')) {
                            if (is_object($_ctx->categories))
                            {
                                $key = $_ctx->categories->cat_desc;
                            } else
                            {
                                $key = $_ctx->posts->cat_desc;
                            }
                            $imgsrc = \dcCatImg::GetImg($key,"m",1);

                            $ret .= '<div class="featured-media">'."\n";
                            $ret .= '<img src="'.$imgsrc.'" alt="'.$rs->cat_title.'" /></div>'."\n";
                            // $ret .= '<p class="legend">'.$rs->post_title.'</p>';
                        }
                        $ret .= '<div class="related-contents">'."\n";
                        $ret .= '<p class="related-category"><a href="'.$rs->getCategoryURL().'">'.$rs->cat_title.'</a></p>'."\n";
                        $ret .= '<p class="related-title"><a href="'.$rs->getURL().'">'.$rs->post_title.'</a></p>'."\n";
                        $ret .= '<p class="post-read-it"><a href="'.$rs->getURL().'" title="Lire '.$rs->post_title.'"><img src="'.$core->blog->settings->system->themes_url.'/'.$core->blog->settings->system->theme.'/img/'.$core->blog->id.'/read-it.png" alt="Lire la suite" /></a></p>'."\n";
                        $ret .= '</div></div>';
                        $count++;
                    }
                    // $ret .= '</div>';
                    echo $ret;
                }
            }
        }
    }*/
}

class tplMagalogueTheme
{
    public static function magalogueEntriesList($attr)
    {
        # Home page last entries selector
    }
    public static function magalogueSliderContent($attr)
    {
        # Entries in home top slider
    }
    public static function magalogueSocialLinks($attr)
    {
        # Social media links
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

        /*if ($core->plugins->moduleExists('relatedEntries')) {
            if ($core->url->type == 'post')
            {

                $s = &$core->blog->settings->relatedEntries;

                if (!$s->relatedEntries_enabled) {
                    return;
                }
                // if (!$s->relatedEntries_afterPost) {
                //  return;
                // }
                if (self::thisPostrelatedEntries($_ctx->posts->post_id) != '') {

                    //related entries
                    $meta = &$GLOBALS['core']->meta;

                    $r_ids = self::thisPostrelatedEntries($_ctx->posts->post_id);
                    $params['post_id'] = $meta->splitMetaValues($r_ids);
                    $rs = $core->blog->getPosts($params);
                        $ret = $core->tpl->includeFile(['src' => '_related-entries.html']);

                    return $ret;
                }
            }
        }*/
        if ($core->plugins->moduleExists('relatedEntries')) {
            if ($core->url->type == 'post')
            {

                $s = &$core->blog->settings->relatedEntries;

                if (!$s->relatedEntries_enabled) {
                    return;
                }
                /*if (self::thisPostrelatedEntries($_ctx->posts->post_id) !== '') {

                    //related entries
                    $meta = &$GLOBALS['core']->meta;

                    $r_ids = self::thisPostrelatedEntries($_ctx->posts->post_id);
                    $posts_id = $meta->splitMetaValues($r_ids);
                    //$params['post_id'] = $meta->splitMetaValues($r_ids);*/

                $lastn = -1;
                if (isset($attr['lastn'])) {
                    $lastn = abs((integer) $attr['lastn']) + 0;
                }

                //$p = "\$meta = &\$core->meta;\n";
                $rel = "if (themes\magalogue\\tplMagalogueTheme::thisPostrelatedEntries(\$_ctx->posts->post_id) !== '') :\n";
                $rel .= "\$meta = &\$GLOBALS['core']->meta;\n";
                $rel .= "\$r_ids = themes\magalogue\\tplMagalogueTheme::thisPostrelatedEntries(\$_ctx->posts->post_id);";

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
            // }
        }
    }
    /*public static function magalogueRelatedEntries($attr)
    {
        global $core;

        return $core->tpl->includeFile(['src' => '_related-entries.html']);
    }*/
}


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
$core->addBehavior('publicEntryAfterContent',[__NAMESPACE__ . '\behaviorMagalogueTheme','publicEntryAfterContent']);

# Templates
$core->tpl->addValue('magalogueEntriesList', [__NAMESPACE__ . '\tplMagalogueTheme', 'magalogueEntriesList']);
$core->tpl->addBlock('magalogueSliderContent', [__NAMESPACE__ . '\tplMagalogueTheme', 'magalogueSliderContent']);
$core->tpl->addValue('magalogueSocialLinks', [__NAMESPACE__ . '\tplMagalogueTheme', 'magalogueSocialLinks']);
//$core->tpl->addValue('magalogueNbEntryPerPage', [__NAMESPACE__ . '\tplMagalogueTheme', 'magalogueNbEntryPerPage']);
$core->tpl->addValue('magalogueLogoSrc', [__NAMESPACE__ . '\tplMagalogueTheme', 'magalogueLogoSrc']);
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
	public static function thisPostrelatedEntries ($id)
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
		global $core, $ctx;
		# Settings

		if ($core->url->type == 'post') {

			if (isset($core->blog->settings->relatedEntries)) {
				$s = &$core->blog->settings->relatedEntries;

				if (!$s->relatedEntries_enabled) {
					return;
				}
				if (self::thisPostrelatedEntries($_ctx->posts->post_id) != '') {

					//related entries
					$meta =& $GLOBALS['core']->meta;

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
									$GLOBALS['featured_i'] = $featured_i; $GLOBALS['featured_f'] = $featured_f;
									$_ctx->file_url = $featured_f->file_url;
									if ($featured_f->media_image) {
										if (isset($featured_f->media_thumb['m']))
										{
											$img_url = $featured_f->media_thumb['m'];
										} else
										{
											$img_url = $featured_f->file_url;
										}
										$img_alt = $featured_f->media_title;

										$ret .= '<div class="featured-media">'."\n";
										$ret .= '<img src="'.$img_url.'" alt="'.$img_alt.'" /></div>'."\n";
									}
								}
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
							$imgsrc = dcCatImg::GetImg($key,"m",1);

							$ret .= '<div class="featured-media">'."\n";
							$ret .= '<img src="'.$imgsrc.'" alt="'.$rs->cat_title.'" /></div>'."\n";
						}
						$ret .= '<div class="related-contents">'."\n";
						$ret .= '<p class="related-category"><a href="'.$rs->getCategoryURL().'">'.$rs->cat_title.'</a></p>'."\n";
						$ret .= '<p class="related-title"><a href="'.$rs->getURL().'">'.$rs->post_title.'</a></p>'."\n";
						$ret .= '<p class="post-read-it"><a href="'.$rs->getURL().'" title="Lire '.$rs->post_title.'"><span class="button">Lire la suite</span></a></p>'."\n";
						$ret .= '</div></div>';
						$count++;
					}
					echo $ret;
				}
			}
		}
	}
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
                        // relative URL (base = img folder of ductile theme)
                        $img_url = $GLOBALS['core']->blog->settings->system->themes_url . '/' . $GLOBALS['core']->blog->settings->system->theme . '/img/' . $s['logo_src'];
                    }
                }
            }
        }

        return $img_url;
    }
}


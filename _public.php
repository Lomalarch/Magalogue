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

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');
//__('Show menu').__('Hide menu').__('Navigation');

$core->url->register('feed/imagesfeed','feed/imagesfeed','^feed/imagesfeed$',array('FeaturedImageFeed','imagesfeed'));
$core->url->register('feed/selectedfeed','feed/selectedfeed','^feed/selectedfeed$',array('FeaturedImageFeed','selectedfeed'));
$core->addBehavior('publicHeadContent',array('behaviorMagalogueTheme','publicHeadContent'));
$core->addBehavior('publicEntryAfterContent',array('behaviorMagalogueTheme','publicEntryAfterContent'));

class behaviorMagalogueTheme
{
	public static function publicHeadContent()
	{
//		global $core,$_ctx;

		echo dcUtils::jsVars(array(
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

		if ($core->url->type == 'post')
		{

			if (isset($core->blog->settings->relatedEntries)) {
				$s = &$core->blog->settings->relatedEntries;

			if (!$s->relatedEntries_enabled) {
				return;
			}
			// if (!$s->relatedEntries_afterPost) {
			// 	return;
			// }
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

									// $ret .= '<div class="related-entry '.($count & 1 ? "" : "odd").'">'."\n";
									$ret .= '<div class="featured-media">'."\n";
									$ret .= '<img src="'.$img_url.'" alt="'.$img_alt.'" /></div>'."\n";
									// 	$ret .= '<div class="related-contents">'."\n";
									// 	$ret .= '<p class="related-category"><a href="'.$rs->getCategoryURL().'">'.$rs->cat_title.'</a></p>'."\n";
									// 	$ret .= '<p class="related-title"><a href="'.$rs->getURL().'">'.$rs->post_title.'</a></p>'."\n";
									// 	$ret .= '<p class="post-read-it"><a href="'.$rs->getURL().'" title="Lire '.$rs->post_title.'"><img src="'.$core->blog->settings->system->themes_url.'/'.$core->blog->settings->system->theme.'/img/'.$core->blog->id.'/read-it.png" alt="Lire la suite" /></a></p>'."\n";
									// 	$ret .= '</div>';
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
						$imgsrc = dcCatImg::GetImg($key,"m",1);

						$ret .= '<div class="featured-media">'."\n";
						$ret .= '<img src="'.$imgsrc.'" alt="'.$rs->cat_title.'" /></div>'."\n";
						// $ret .= '<p class="legend">'.$rs->post_title.'</p>';
					}
					$ret .= '<div class="related-contents">'."\n";
					$ret .= '<p class="related-category"><a href="'.$rs->getCategoryURL().'">'.$rs->cat_title.'</a></p>'."\n";
					$ret .= '<p class="related-title"><a href="'.$rs->getURL().'">'.$rs->post_title.'</a></p>'."\n";
					$ret .= '<p class="post-read-it"><a href="'.$rs->getURL().'" title="Lire '.$rs->post_title.'"><span class="button">Lire la suite</span></a></p>'."\n";
					$ret .= '</div></div>';
					$count++;
				}
				// $ret .= '</div>';
				echo $ret;
			}
		}
		}
	}
}
class FeaturedImageFeed extends dcUrlHandlers
{
	public static function imagesfeed()
	{
		global $core;

		// $_ctx->nb_entry_per_page = $core->blog->settings->nb_post_per_feed;

		$mime = 'application/atom+xml';
		self::serveDocument('imagesfeed.xml',$mime);
	}
	public static function selectedfeed()
	{
		global $core;

		// $_ctx->nb_entry_per_page = $core->blog->settings->nb_post_per_feed;

		$mime = 'application/atom+xml';
		self::serveDocument('selectedfeed.xml',$mime);
	}
}

// $core->addBehavior('publicEntryAfterContent',array('addToAny','entryAfterContent'));
// $core->addBehavior('publicFooterContent',array('addToAny','footerContent'));
// class addToAny
// {

//     public static function entryAfterContent($cur,$post_id) {
//     global $_ctx;
//     echo '<!-- AddToAny BEGIN -->
//     <div class="a2a_kit a2a_kit_size_32 a2a_default_style">
//     <a class="a2a_button_facebook"></a>
//     <a class="a2a_button_pinterest"></a>
//     <a class="a2a_button_twitter"></a>
//     <a class="a2a_button_google_plus"></a>
//     <a class="a2a_button_email"></a>
//     <a class="a2a_dd" href="https://www.addtoany.com/share"></a>
//     </div>
//     <!-- AddToAny END -->';
// //     return $addThisButtons;
//     }
//     public static function footerContent($cur,$post_id) {
//     echo '<script type="text/javascript">
//     var a2a_config = a2a_config || {};
//     a2a_config.locale = "fr";
// </script>
// <script type="text/javascript" async src="https://static.addtoany.com/menu/page.js"></script>';
//     }
// }
// $core->addBehavior('publicEntryAfterContent',array('addThis','entryAfterContent'));
// $core->addBehavior('publicFooterContent',array('addThis','footerContent'));

// class addThis
// {

//     public static function entryAfterContent($cur,$post_id) {
//     global $_ctx;
//     echo '<!-- AddThis Button BEGIN -->
//     <div class="addthis_toolbox addthis_default_style addthis_32x32_style">
//       <a class="addthis_button_facebook" addthis:url="'.$_ctx->posts->getURL().'" addthis:title="'.$_ctx->posts->post_title.'"></a>
//       <a class="addthis_button_twitter" addthis:url="'.$_ctx->posts->getURL().'" addthis:title="'.$_ctx->posts->post_title.'"></a>
//       <a class="addthis_button_pinterest_share" addthis:url="'.$_ctx->posts->getURL().'" addthis:title="'.$_ctx->posts->post_title.'"></a>
//       <a class="addthis_button_google_plusone_share" addthis:url="'.$_ctx->posts->getURL().'" addthis:title="'.$_ctx->posts->post_title.'"></a>
//       <a class="addthis_button_email" addthis:url="'.$_ctx->posts->getURL().'" addthis:title="'.$_ctx->posts->post_title.'"></a>
//       <a class="addthis_button_compact" addthis:url="'.$_ctx->posts->getURL().'" addthis:title="'.$_ctx->posts->post_title.'"></a>
//     </div>
//     <!-- AddThis Button END -->';
// //     return $addThisButtons;
//     }
//     public static function footerContent($cur,$post_id) {
//     echo '<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js"></script>';
//     }
// }

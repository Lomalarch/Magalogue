<?php
/**
 * @brief Magalogue, a theme for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Themes
 *
 * @copyright Noé Cendrier
 * @copyright AGPL-3.0
 */

 /* Fonctions prévues :
 # Couleur de mise en valeur
 # Contenu du block slick
 # Contenu du bloc dernières publi
 # Bannière
 # Liens RS
 */

namespace Dotclear\Theme\magalogue;

use Dotclear\App;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Backend\ThemeConfig;
use Dotclear\Core\Process;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Network\Http;
use Exception;
use form;

/**
 * @brief   The module configuration process.
 * @ingroup ductile
 */
class Config extends Process
{
    public static function init(): bool
    {
        // limit to backend permissions
        if (!self::status(My::checkContext(My::CONFIG))) {
            return false;
        }

        // load locales
        My::l10n('admin');

        // if (preg_match('#^http(s)?://#', (string) App::blog()->settings()->system->themes_url)) {
        //     App::backend()->img_url_link = Http::concatURL(App::blog()->settings()->system->themes_url, '/' . App::blog()->settings()->system->theme . '/img/links/');
        // } else {
            App::backend()->img_url_link = My::fileURL('/img/links/');
        // }

        $img_path_links = My::path() . '/img/links/';
        $tpl_path = My::path() . '/tpl/';
        $theme_path = My::path();

        App::backend()->standalone_config = (bool) App::themes()->moduleInfo(App::blog()->settings()->system->theme, 'standalone_config');

        // Load contextual help
        App::themes()->loadModuleL10Nresources(My::id(), App::lang()->getLang());

        App::backend()->slider_list_types = [
            __('Selected') => 'selected',
            __('First level categories') => 'first-level-categories',
            __('All categories')  => 'categories',
            __('Last entries') => 'recent'
        ];
        // Get all _home-slider-*.html in tpl folder of theme
        $list_types_templates = files::scandir($tpl_path);
        if (is_array($list_types_templates)) {
            foreach ($list_types_templates as $v) {
                if (preg_match('/^_home\-slider\-(.*)\.html$/', $v, $m)) {
                    if (isset($m[1])) {
                        if (!in_array($m[1], App::backend()->slider_list_types)) {
                            // template not already in full list
                            App::backend()->slider_list_types[__($m[1])] = $m[1];
                        }
                    }
                }
            }
        }
        App::backend()->links_colors = [
            __('Blue') => 'blue',
            __('Green') => 'green',
            __('Purple') => 'purple',
            __('Orange') => 'orange'
        ];
        // Get all color-*.css
        App::backend()->links_colors_css = files::scandir($theme_path);
        if (is_array(App::backend()->links_colors_css)) {
            foreach (App::backend()->links_colors_css as $v) {
                if (preg_match('/^color\-(.*)\.css$/', $v, $m)) {
                    if (isset($m[1])) {
                        if (!in_array($m[1], App::backend()->links_colors)) {
                            // css not already in full list
                            App::backend()->links_colors[__($m[1])] = $m[1];
                        }
                    }
                }
            }
        }
        App::backend()->contexts = [
            'slider'      => __('Homepage slider'),
            'list'      => __('Homepage displayed posts')
        ];


        $magalogue_base = [
            // HTML
            'logo_src'    => null,
            'no_logo'     => null,
            // CSS
            'links_color' => null
        ];

        $magalogue_lists_base = [
            'slider'      => 'selected',
            'list'        => 'first-level-categories'
        ];

        App::backend()->magalogue_counts_base = [
            'slider'      => null,
            'list'        => null
        ];

        App::backend()->magalogue_user = App::blog()->settings()->themes->get(App::blog()->settings()->system->theme . '_style');
        App::backend()->magalogue_user = @unserialize(App::backend()->magalogue_user);
        if (!is_array(App::backend()->magalogue_user)) {
            App::backend()->magalogue_user = [];
        }
        App::backend()->magalogue_user = [...$magalogue_base, ...App::backend()->magalogue_user];
        App::backend()->magalogue_lists = App::blog()->settings()->themes->get(App::blog()->settings()->system->theme . '_entries_lists');
        App::backend()->magalogue_lists = @unserialize(App::backend()->magalogue_lists);
        if (!is_array(App::backend()->magalogue_lists)) {
            App::backend()->magalogue_lists = $magalogue_lists_base;
        }
        App::backend()->magalogue_lists = [...$magalogue_lists_base, ...App::backend()->magalogue_lists];

        App::backend()->magalogue_counts = App::blog()->settings()->themes->get(App::blog()->settings()->system->theme . '_entries_counts');
        App::backend()->magalogue_counts = @unserialize(App::backend()->magalogue_counts);
        if (!is_array(App::backend()->magalogue_counts)) {
            App::backend()->magalogue_counts = App::backend()->magalogue_counts_base;
        }
        App::backend()->magalogue_counts = [...App::backend()->magalogue_counts_base, ...App::backend()->magalogue_counts];

        App::backend()->magalogue_stickers = App::blog()->settings()->themes->get(App::blog()->settings()->system->theme . '_stickers');
        App::backend()->magalogue_stickers = @unserialize(App::backend()->magalogue_stickers);

        // if (!$magalogue_base['links_color'])

        // If no stickers defined, add feed Atom one
        if (!is_array(App::backend()->magalogue_stickers)) {
            App::backend()->magalogue_stickers = [[
                'label' => __('Subscribe'),
                'url'   => App::blog()->url .
                App::url()->getURLFor('feed', 'atom'),
                'image' => 'rss.svg'
            ]];
        }

        $magalogue_stickers_full = [];
        // Get all sticker images already used
        if (is_array(App::backend()->magalogue_stickers)) {
            foreach (App::backend()->magalogue_stickers as $v) {
                $magalogue_stickers_full[] = $v['image'];
            }
        }
        App::backend()->magalogue_stickers_full = $magalogue_stickers_full;
        $magalogue_stickers = App::backend()->magalogue_stickers;
        // Get all *.svg in img/links folder of theme
        $magalogue_stickers_images = files::scandir($img_path_links);
        if (is_array($magalogue_stickers_images)) {
            foreach ($magalogue_stickers_images as $v) {
                if (preg_match('/^(.*)\.svg$/', $v)) {
                    if (!in_array($v, $magalogue_stickers_full)) {
                        // image not already used
                        $magalogue_stickers[] = [
                            'label' => preg_replace('/\.svg$/', '', $v),
                            'url'   => null,
                            'image' => $v];
                    }
                }
            }
        }
        App::backend()->magalogue_stickers = $magalogue_stickers;

        return true;
    }
    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (!empty($_POST)) {
            try
            {
                # HTML
                $magalogue_user = App::backend()->magalogue_user;

                $magalogue_user['logo_src'] = $_POST['logo_src'];
                $magalogue_user['no_logo']  = (integer) !empty($_POST['no_logo']);
                $magalogue_user['links_color'] = $_POST['links_color'];

                App::backend()->magalogue_user = $magalogue_user;

                $magalogue_stickers = [];
                for ($i = 0; $i < count($_POST['sticker_image']); $i++) {
                    $magalogue_stickers[] = [
                        'label' => $_POST['sticker_label'][$i],
                        'url'   => $_POST['sticker_url'][$i],
                        'image' => $_POST['sticker_image'][$i]
                    ];
                }

                $order = [];
                if (empty($_POST['ds_order']) && !empty($_POST['order'])) {
                    $order = $_POST['order'];
                    asort($order);
                    $order = array_keys($order);
                }
                if (!empty($order)) {
                    $new_magalogue_stickers = [];
                    foreach ($order as $i => $k) {
                        $new_magalogue_stickers[] = [
                            'label' => $magalogue_stickers[$k]['label'],
                            'url'   => $magalogue_stickers[$k]['url'],
                            'image' => $magalogue_stickers[$k]['image']
                        ];
                    }
                    $magalogue_stickers = $new_magalogue_stickers;
                }
                App::backend()->magalogue_stickers = $magalogue_stickers;

                // We do no wish to save unused stickers
                foreach ($magalogue_stickers as $k => $s) {
                    if (empty ($s['label']) || empty ($s['url'])) {
                        unset($magalogue_stickers[$k]);
                    }
                }

                $magalogue_lists = App::backend()->magalogue_lists;
                for ($i = 0; $i < count($_POST['list_type']); $i++) {
                    $magalogue_lists[$_POST['list_ctx'][$i]] = $_POST['list_type'][$i];
                }
                App::backend()->magalogue_lists = $magalogue_lists;

                $magalogue_counts = App::backend()->magalogue_counts;
                for ($i = 0; $i < count($_POST['count_nb']); $i++) {
                    $magalogue_counts[$_POST['count_ctx'][$i]] = $_POST['count_nb'][$i];
                }
                App::backend()->magalogue_counts = $magalogue_counts;
                    
                App::blog()->settings()->addNamespace('themes');
                App::blog()->settings()->themes->put(App::blog()->settings()->system->theme . '_style', serialize(App::backend()->magalogue_user));
                App::blog()->settings()->themes->put(App::blog()->settings()->system->theme . '_stickers', serialize($magalogue_stickers));
                App::blog()->settings()->themes->put(App::blog()->settings()->system->theme . '_entries_lists', serialize(App::backend()->magalogue_lists));
                App::blog()->settings()->themes->put(App::blog()->settings()->system->theme . '_entries_counts', serialize(App::backend()->magalogue_counts));

                // Blog refresh
                App::blog()->triggerBlog();

                // Template cache reset
                App::cache()->emptyTemplatesCache();

                Notices::message(__('Theme configuration upgraded.'), true, true);
            } catch (Exception $e) {
               App::error()->add($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        // Helpers

        $fontDef = fn ($c) => isset(App::backend()->font_families[$c]) ?
            '<abbr title="' . Html::escapeHTML(App::backend()->font_families[$c]) . '"> ' . __('Font family') . ' </abbr>' :
            '';

        // Legacy mode
        if (!App::backend()->standalone_config) {
            echo '</form>';
        }

        // HTML Tab

        echo
        // '<div class="multi-part" id="themes-list' . (App::backend()->conf_tab === 'html' ? '' : '-html') . '" title="' . __('Content') . '">' .
        '<form id="theme_config" action="' . App::backend()->url()->get('admin.blog.theme', ['conf' => '1']) .
        '" method="post" enctype="multipart/form-data">' .
    
        '<h4 class="pretty-title">' . __('Header') . '</h4>' .
        '<p class="field"><label for="logo_src">' . __('Logo URL:') . '</label> ' . 
        form::field('logo_src', 40, 255, App::backend()->magalogue_user['logo_src']) . '</p>' .
        '<p class="field"><label for="no_logo">' . __('No logo (display Blog name):') . '</label> ' .
        form::checkbox('no_logo', 1, App::backend()->magalogue_user['no_logo']);
    
    
        if (App::plugins()->moduleExists('simpleMenu')) {
            echo
            '<p>' .
            sprintf(
                __('To configure the top menu go to the <a href="%s">Simple Menu administration page</a>.'),
                App::backend()->url()->get('admin.plugin.simpleMenu')
            ) .
            '</p>';
        }

        echo '<h4 class="border-top pretty-title">' . __('Links color') . '</h4>';

        echo 
        '<p class="field"><label for="links_color">' . __('Select the base highlight color') . '</label>';
        // if (!isset(App::backend()->magalogue_user['links_color'])) { // Green is the default color
        //     App::backend()->magalogue_user['links_color'] = 'green';
        // }
        echo form::combo(['links_color'], App::backend()->links_colors, App::backend()->magalogue_user['links_color'] ?? 'green'); 
        echo '<h4 class="border-top pretty-title">' . __('Social links') . '</h4>';
        
        echo
        '<div class="table-outer">' .
        '<table class="dragable">' . '<caption>' . __('Social links (header)') . '</caption>' .
        '<thead>' .
        '<tr>' .
        '<th scope="col">' . '</th>' .
        '<th scope="col">' . __('Image') . '</th>' .
        '<th scope="col">' . __('Label') . '</th>' .
        '<th scope="col">' . __('URL') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody id="stickerslist">';
        $count = 0;
        foreach (App::backend()->magalogue_stickers as $i => $v) {
            $count++;
            $v['service'] = !empty($v['image']) ? str_replace('.svg', '', $v['image']) : null;
            echo
            '<tr class="line" id="l_' . $i . '">' .
            '<td class="handle minimal">' . form::number(['order[' . $i . ']'], [
                'min'     => 0,
                'max'     => count(App::backend()->magalogue_stickers),
                'default' => $count,
                'class'   => 'position'
            ]) .
            form::hidden(['dynorder[]', 'dynorder-' . $i], $i) . '</td>' .
            '<td class="linkimg">' . form::hidden(['sticker_image[]'], $v['image']) . '<img src="' . App::backend()->img_url_link . $v['image'] . '" alt="' . $v['service'] . '" title="' . $v['service'] . '" style="max-height: 1.2em;"/> ' . '</td>' .
            '<td scope="row">' . form::field(['sticker_label[]', 'dsl-' . $i], 20, 255, $v['label']) . '</td>' .
            '<td>' . form::field(['sticker_url[]', 'dsu-' . $i], 40, 255, $v['url']) . '</td>' .
                '</tr>';
        }
        echo
            '</tbody>' .
            '</table></div>';
        
        echo '<h4 class="border-top pretty-title">' . __('Entries lists in homepage') . '</h4>';
        
        echo '<table id="entrieslist">' . '<caption class="hidden">' . __('Entries lists') . '</caption>' .
        '<thead>' .
        '<tr>' .
        '<th scope="col">' . __('Context') . '</th>' .
        '<th scope="col">' . __('Entries list type') . '</th>' .
        '<th scope="col">' . __('Number of entries (Selected and Last entries only)') . '</th>' .
        '</tr>' .
        '</thead>' .
        '<tbody>';
        foreach (App::backend()->contexts as $k => $v) {
            echo
            '<tr>' .
            '<td scope="row">' . App::backend()->contexts[$k] . '</td>' .
            '<td>' . form::hidden(['list_ctx[]'], $k) . form::combo(['list_type[]'], App::backend()->slider_list_types, App::backend()->magalogue_lists[$k]) . '</td>';
            if (array_key_exists($k, App::backend()->magalogue_counts)) {
                echo '<td>' . form::hidden(['count_ctx[]'], $k) . form::number(['count_nb[]'], [
                    'min'     => 0,
                    'max'     => 999,
                    'default' => App::backend()->magalogue_counts[$k]
                ]) . '</td>';
            } else {
                echo '<td></td>';
            }
            echo
                '</tr>';
        }
        echo
            '</tbody>' .
            '</table>';
        
        echo '<p class="clear">' . form::hidden('ds_order', '') . '<input type="submit" value="' . __('Save') . '" />' . App::nonce()->getFormNonce() . '</p>';
        echo '</form>';
        
        
        Page::helpBlock('magalogue');
        
        // Legacy mode
        if (!App::backend()->standalone_config) {
            echo '<form style="display:none">';
        }
    }
}

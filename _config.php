<?php
/**
 * @brief Magalogue, a theme for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Themes
 *
 * @copyright Noé Cendrier
 * @copyright GPL-2.0-only
 */

 /* Fonctions prévues :
 # Couleur de mise en valeur
 # Contenu du block slick
 # Contenu (et titre) du bloc dernières publi
 # Bannière
 # Liens RS
 */

if (!defined('DC_CONTEXT_ADMIN')) {return;}

l10n::set(dirname(__FILE__) . '/locales/' . $_lang . '/admin');

if (preg_match('#^http(s)?://#', $core->blog->settings->system->themes_url)) {
    $img_url = http::concatURL($core->blog->settings->system->themes_url, '/' . $core->blog->settings->system->theme . '/img/');
} else {
    $img_url = http::concatURL($core->blog->url, $core->blog->settings->system->themes_url . '/' . $core->blog->settings->system->theme . '/img/');
}
$img_path = dirname(__FILE__) . '/img/';

$tpl_path = dirname(__FILE__) . '/tpl/';

$standalone_config = (boolean) $core->themes->moduleInfo($core->blog->settings->system->theme, 'standalone_config');

// Load contextual help
if (file_exists(dirname(__FILE__) . '/locales/' . $_lang . '/resources.php')) {
    require dirname(__FILE__) . '/locales/' . $_lang . '/resources.php';
}

$slider_list_types = [
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
                if (!in_array($m[1], $slider_list_types)) {
                    // template not already in full list
                    $slider_list_types[__($m[1])] = $m[1];
                }
            }
        }
    }
}

$contexts = [
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

$magalogue_counts_base = [
    'slider'      => null,
    'list'        => null
];

$magalogue_user = $core->blog->settings->themes->get($core->blog->settings->system->theme . '_style');
$magalogue_user = @unserialize($magalogue_user);
if (!is_array($magalogue_user)) {
    $magalogue_user = [];
}
$magalogue_user = array_merge($magalogue_base, $magalogue_user);

$magalogue_lists = $core->blog->settings->themes->get($core->blog->settings->system->theme . '_entries_lists');
$magalogue_lists = @unserialize($magalogue_lists);
if (!is_array($magalogue_lists)) {
    $magalogue_lists = $magalogue_lists_base;
}
$magalogue_lists = array_merge($magalogue_lists_base, $magalogue_lists);

$magalogue_counts = $core->blog->settings->themes->get($core->blog->settings->system->theme . '_entries_counts');
$magalogue_counts = @unserialize($magalogue_counts);
if (!is_array($magalogue_counts)) {
    $magalogue_counts = $magalogue_counts_base;
}
$magalogue_counts = array_merge($magalogue_counts_base, $magalogue_counts);

$magalogue_stickers = $core->blog->settings->themes->get($core->blog->settings->system->theme . '_stickers');
$magalogue_stickers = @unserialize($magalogue_stickers);

// If no stickers defined, add feed Atom one
if (!is_array($magalogue_stickers)) {
    $magalogue_stickers = [[
        'label' => __('Subscribe'),
        'url'   => $core->blog->url .
        $core->url->getURLFor('feed', 'atom'),
        'image' => 'rss-link.png'
    ]];
}

$magalogue_stickers_full = [];
// Get all sticker images already used
if (is_array($magalogue_stickers)) {
    foreach ($magalogue_stickers as $v) {
        $magalogue_stickers_full[] = $v['image'];
    }
}
// Get all *-link.png in img folder of theme
$magalogue_stickers_images = files::scandir($img_path);
if (is_array($magalogue_stickers_images)) {
    foreach ($magalogue_stickers_images as $v) {
        if (preg_match('/^(.*)-link\.png$/', $v)) {
            if (!in_array($v, $magalogue_stickers_full)) {
                // image not already used
                $magalogue_stickers[] = [
                    'label' => null,
                    'url'   => null,
                    'image' => $v];
            }
        }
    }
}

$conf_tab = isset($_POST['conf_tab']) ? $_POST['conf_tab'] : 'html';

if (!empty($_POST)) {
    try
    {
        # HTML
            $magalogue_user['logo_src'] = $_POST['logo_src'];
            $magalogue_user['no_logo']  = (integer) !empty($_POST['no_logo']);

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

            for ($i = 0; $i < count($_POST['list_type']); $i++) {
                $magalogue_lists[$_POST['list_ctx'][$i]] = $_POST['list_type'][$i];
            }

            for ($i = 0; $i < count($_POST['count_nb']); $i++) {
                $magalogue_counts[$_POST['count_ctx'][$i]] = $_POST['count_nb'][$i];
            }

        $core->blog->settings->addNamespace('themes');
        $core->blog->settings->themes->put($core->blog->settings->system->theme . '_style', serialize($magalogue_user));
        $core->blog->settings->themes->put($core->blog->settings->system->theme . '_stickers', serialize($magalogue_stickers));
        $core->blog->settings->themes->put($core->blog->settings->system->theme . '_entries_lists', serialize($magalogue_lists));
        $core->blog->settings->themes->put($core->blog->settings->system->theme . '_entries_counts', serialize($magalogue_counts));

        // Blog refresh
        $core->blog->triggerBlog();

        // Template cache reset
        $core->emptyTemplatesCache();

        dcPage::message(__('Theme configuration upgraded.'), true, true);
    } catch (Exception $e) {
        $core->error->add($e->getMessage());
    }
}

// Legacy mode
if (!$standalone_config) {
    echo '</form>';
}

echo '<form id="theme_config" action="' . $core->adminurl->get('admin.blog.theme', ['conf' => '1']) .
    '" method="post" enctype="multipart/form-data">';

echo '<h4 class="pretty-title">' . __('Header') . '</h4>';
echo '<p class="field"><label for="logo_src">' . __('Logo URL:') . '</label> ' .
form::field('logo_src', 40, 255, $magalogue_user['logo_src']) . '</p>';
echo '<p class="field"><label for="no_logo">' . __('No logo (display Blog name):') . '</label> ' .
form::checkbox('no_logo', 1, $magalogue_user['no_logo']);

if ($core->plugins->moduleExists('simpleMenu')) {
    echo '<p>' . sprintf(__('To configure the top menu go to the <a href="%s">Simple Menu administration page</a>.'),
        $core->adminurl->get('admin.plugin.simpleMenu')) . '</p>';
}
echo '<h4 class="border-top pretty-title">' . __('Links color') . '</h4>';

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
foreach ($magalogue_stickers as $i => $v) {
    $count++;
    $v['service'] = str_replace('-link.png', '', $v['image']);
    echo
    '<tr class="line" id="l_' . $i . '">' .
    '<td class="handle minimal">' . form::number(['order[' . $i . ']'], [
        'min'     => 0,
        'max'     => count($magalogue_stickers),
        'default' => $count,
        'class'   => 'position'
    ]) .
    form::hidden(['dynorder[]', 'dynorder-' . $i], $i) . '</td>' .
    '<td class="linkimg">' . form::hidden(['sticker_image[]'], $v['image']) . '<img src="' . $img_url . $v['image'] . '" alt="' . $v['service'] . '" title="' . $v['service'] . '" /> ' . '</td>' .
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
'<th scope="col">' . __('Number of entries') . '</th>' .
    '</tr>' .
    '</thead>' .
    '<tbody>';
foreach ($magalogue_lists as $k => $v) {
    echo
    '<tr>' .
    '<td scope="row">' . $contexts[$k] . '</td>' .
    '<td>' . form::hidden(['list_ctx[]'], $k) . form::combo(['list_type[]'], $slider_list_types, $v) . '</td>';
    if (array_key_exists($k, $magalogue_counts)) {
        echo '<td>' . form::hidden(['count_ctx[]'], $k) . form::number(['count_nb[]'], [
            'min'     => 0,
            'max'     => 999,
            'default' => $magalogue_counts[$k]
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

// echo '<p><input type="hidden" name="conf_tab" value="html" /></p>';
echo '<p class="clear">' . form::hidden('ds_order', '') . '<input type="submit" value="' . __('Save') . '" />' . $core->formNonce() . '</p>';
echo '</form>';


dcPage::helpBlock('magalogue');

// Legacy mode
if (!$standalone_config) {
    echo '<form style="display:none">';
}

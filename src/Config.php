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

declare(strict_types=1);

namespace Dotclear\Theme\magalogue;

use Dotclear\App;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Img;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Number;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Set;
use Dotclear\Helper\Html\Form\None;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Process\TraitProcess;
use Exception;

/**
 * @brief   The module configuration process.
 * @ingroup ductile
 */
class Config
{
    use TraitProcess;

    public static function init(): bool
    {
        // limit to backend permissions
        if (!self::status(My::checkContext(My::CONFIG))) {
            return false;
        }

        // load locales
        My::l10n('admin');

        App::backend()->img_url_link = My::fileURL('/img/links/');

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
                        $links_colors = App::backend()->links_colors;
                        if (!in_array($m[1], $links_colors)) {
                            // css not already in full list
                            $links_colors[__($m[1])] = $m[1];
                        }
                        App::backend()->links_colors = $links_colors;
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

                $magalogue_user['logo_src'] = $_POST['logo_src'] ?? '';
                $magalogue_user['no_logo']  = (int) !empty($_POST['no_logo']);
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
                    
                App::blog()->settings()->addWorkspace('themes');
                App::blog()->settings()->themes->put(App::blog()->settings()->system->theme . '_style', serialize(App::backend()->magalogue_user));
                App::blog()->settings()->themes->put(App::blog()->settings()->system->theme . '_stickers', serialize($magalogue_stickers));
                App::blog()->settings()->themes->put(App::blog()->settings()->system->theme . '_entries_lists', serialize(App::backend()->magalogue_lists));
                App::blog()->settings()->themes->put(App::blog()->settings()->system->theme . '_entries_counts', serialize(App::backend()->magalogue_counts));

                // Blog refresh
                App::blog()->triggerBlog();

                // Template cache reset
                App::cache()->emptyTemplatesCache();

                App::backend()->notices()->message(__('Theme configuration upgraded.'), true, true);
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

        // Loop values

        $social_links = function () {
            $count = 0;
            foreach (App::backend()->magalogue_stickers as $i => $v) {
                $count++;
                $v['service'] = !empty($v['image']) ? str_replace('.svg', '', $v['image']) : null;
                yield (new Tr('1_' . $i))
                    ->cols([
                        (new Td())
                            ->class('handle minimal')
                            ->items([
                                (new Number(['order[' . $i . ']'], 0, count(App::backend()->magalogue_stickers), $count))
                                    ->class('position'),
                                (new Hidden(['dynorder[]', 'dynorder-' . $i], (string) $i)),
                            ]),
                        (new Td())
                            ->class('linkimg')
                            ->items([
                                (new Hidden(['sticker_image[]'], $v['image'])),
                                (new Img(App::backend()->img_url_link . $v['image']))
                                    ->alt($v['service'])
                                    ->title($v['service'])
                                    ->extra('style="max-height: 1.2em"')
                            ]),
                        (new Td())
                            ->items([
                                (new Input(['sticker_label[]', 'dsl-' . $i]))
                                    ->size(20)
                                    ->maxlength(255)
                                    ->default($v['label'])
                            ]),
                        (new Td())
                            ->items([
                                (new Input(['sticker_url[]', 'dsu-' . $i]))
                                    ->size(40)
                                    ->maxlength(255)
                                    ->default($v['url'])
                            ])
                    ]);
            }

        };

        $home_content = function () {
            foreach (App::backend()->contexts as $k => $v) {
                yield (new Tr())
                    ->items([
                        (new Td())
                            ->scope('row')
                            ->text(App::backend()->contexts[$k]),
                        (new Td())
                            ->items([
                                (new Hidden('list_ctx[]', 'slider')),
                                (new Select('list_type[]'))
                                    ->items(App::backend()->slider_list_types)
                                    ->default(App::backend()->magalogue_lists[$k])
                            ]),
                        (new Td())
                            ->items([
                                array_key_exists($k, App::backend()->magalogue_counts) ?
                                    (new Set())
                                        ->items([
                                            (new Hidden(['count_ctx[]'], $k)),
                                            (new Number(['count_nb[]'], 0, 999, (int) App::backend()->magalogue_counts[$k])),
                                        ]) :
                                    (new None()),

                            ])
                    ]);
            }
        };

        // Page content

        echo (new Set())
            ->items([
                (new Fieldset())
                    ->legend((new Legend((__('Header')))))
                    ->items([
                        (new Para())
                            ->items([
                                (new Input())
                                    ->id('logo_src')
                                    ->value(App::backend()->magalogue_user['logo_src'])
                                    ->label((new Label(__('Logo URL:'), Label::OL_TF)))
                            ])->class('field'),
                        (new Para())
                            ->items([
                                (new Checkbox('no_logo'))
                                    ->label((new Label(__('No logo (display Blog name):'), Label::OL_TF)))
                                    ->checked(App::backend()->magalogue_user['no_logo'])
                            ]),
                        App::plugins()->moduleExists('simpleMenu') ?
                            (new Note())
                                ->text(sprintf(
                                    __('To configure the top menu go to the <a href="%s">Simple Menu administration page</a>.'),
                                    App::backend()->url()->get('admin.plugin.simpleMenu')
                                ))
                                ->class(['form-note', 'info']) :
                            (new None()),
                    ]),
                (new Fieldset())
                    ->legend((new Legend(__('Links color'))))
                    ->items([
                        (new Para)
                        ->class('field')
                        ->items([
                            (new Select('links_color'))
                                ->items(App::backend()->links_colors)
                                ->default((App::backend()->magalogue_user['links_color'] ?? 'green'))
                                ->label((new Label(__('Select the base highlight color'), Label::OL_TF)))
                            ])
                    ]),
                (new Fieldset())
                    ->legend((new Legend(__('Social links'))))
                    ->items([
                        (new Table())
                            ->class('dragable')
                            ->caption((new Caption(__('Social links (header)'))))
                            ->thead((new Thead())
                                ->items([
                                    (new Tr())
                                        ->items([
                                            (new Th())
                                                ->scope('col'),
                                            (new Th())
                                                ->text(__('Image'))
                                                ->scope('col'),
                                            (new Th())
                                                ->text(__('Label'))
                                                ->scope('col'),
                                            (new Th())
                                                ->text(__('URL'))
                                                ->scope('col'),
                                        ]),
                                ]))
                            ->tbody((new Tbody('stickerslist'))
                                ->items([
                                    ...$social_links(),
                                ])),


                    ])
                    ->class('table_outer'),
                (new Fieldset())
                    ->legend(new Legend((__('Entries lists in homepage'))))
                    ->items([
                        (new Table('entrieslist'))
                            ->caption((new Caption(__('Entries lists')))
                                ->class('hidden'))
                            ->thead((new Thead())
                                ->items([
                                    (new Tr())
                                        ->items([
                                            (new Th())
                                                ->scope('col')
                                                ->text(__('Context')),
                                            (new Th())
                                                ->scope('col')
                                                ->text(__('Entries list type')),
                                            (new Th())
                                                ->scope('col')
                                                ->text(__('Number of entries (Selected and Last entries only)')),
                                        ])
                                ]))
                            ->tbody((new Tbody())
                                ->items([
                                    ...$home_content(),
                                ]))
                    ])
            ])->render();

        App::backend()->page()->helpBlock('magalogue');
    }
}

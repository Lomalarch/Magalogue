<?php
/**
 * @package     Dotclear
 *
 * @copyright   Noé Cendrier & Julien Jakoby
 * @copyright   AGPL-3.0
 */

namespace Dotclear\Theme\magalogue;

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\Html\Html;

/**
 * @brief   The module frontend process.
 * @ingroup magalogue
 */
class Frontend extends Process
{
    /**
     * Init the process.
     *
     * @return     bool
     */
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    /**
     * Processes
     *
     * @return     bool
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        # Behaviors
        App::behavior()->addBehaviors([
            'publicHeadContent'  => self::publicHeadContent(...),
            'templateBeforeBlockV2' => self::templateBeforeBlock(...),
        ]);

        # Templates
        App::frontend()->template()->addValue('magalogueEntriesList', self::magalogueEntriesList(...));
        App::frontend()->template()->addValue('magalogueSliderContent', self::magalogueSliderContent(...));
        App::frontend()->template()->addValue('magalogueSocialLinks', self::magalogueSocialLinks(...));
        App::frontend()->template()->addValue('magalogueBanner', self::magalogueBanner(...));
        App::frontend()->template()->addBlock('magalogueRelatedEntries', self::magalogueRelatedEntries(...));

        return true;
    }

    /**
     * Public head content behavior callback
     *
     * @return void
     */
    public static function publicHeadContent(): void
    {
        echo Html::jsJson('dotclear_magalogue', [
            'show_menu'  => __('Show menu'),
            'hide_menu'  => __('Hide menu'),
            'navigation' => __('Main menu')
        ]);
        if (App::blog()->settings()->themes->get(App::blog()->settings()->system->theme . '_style')) {
            $s = App::blog()->settings()->themes->get(App::blog()->settings()->system->theme . '_style');
            $s = @unserialize($s);
            if (!empty($s['links_color']) && $s['links_color'] !== 'green') {
                echo 
                '<link rel="stylesheet" type="text/css" href="' . My::fileURL('/color-' . $s['links_color'] . '.css') . '">
                <link rel="stylesheet" type="text/css" href="' . My::fileURL('/variant-color.css') . '">';
            }
        }
        
    }

    /**
     * Filter Entries for Home page (slider and list)
     *
     * @param   string                      $b      The block
     * @param   ArrayObject<string, mixed>  $attr   The attribute
     * @return string
     */
    public static function templateBeforeBlock(string $b, ArrayObject $attr): string
    {
        # Number of entries in block
        if ($b == 'Entries' && (isset($attr['maga_id']) && $attr['maga_id'] == 'slider')) {
            return '<?php' . "\n" .
            'if (App::blog()->settings->themes->get(App::blog()->settings->system->theme . \'_entries_counts\')) {'  . "\n" .
                '$c = App::blog()->settings->themes->get(App::blog()->settings->system->theme . \'_entries_counts\');'  . "\n" .
                '$c = @unserialize($c);'  . "\n" .
                'if (is_array($c)) {' . "\n" .
                    '$c = $c[\'slider\'];' . "\n" .
                '}' . "\n" .
                'else {' . "\n" .
                    '$c = 5;' . "\n" .
                '}' . "\n" .
            '} ' . "\n" .
            'else {' . "\n" .
                '$c = 5;' . "\n" .
            '}' . "\n" .
            'App::frontend()->context()->nb_entry_first_page = $c;' . "\n" .
           '?>' . "\n";
        }
        if ($b == 'Entries' && (isset($attr['maga_id']) && $attr['maga_id'] == 'list')) {
            return '<?php' . "\n" .
            'if (App::blog()->settings->themes->get(App::blog()->settings->system->theme . \'_entries_counts\')) {'  . "\n" .
                '$c = App::blog()->settings->themes->get(App::blog()->settings->system->theme . \'_entries_counts\');'  . "\n" .
                '$c = @unserialize($c);'  . "\n" .
                'if (is_array($c)) {' . "\n" .
                    '$c = $c[\'list\'];' . "\n" .
                '}' . "\n" .
                'else {' . "\n" .
                    '$c = 9;' . "\n" .
                '}' . "\n" .
            '} ' . "\n" .
            'else {' . "\n" .
                '$c = 9;' . "\n" .
            '}' . "\n" .
            'App::frontend()->context()->nb_entry_first_page = $c;' . "\n" .
                    '?>'  . "\n";
            ;
        }
        return '';
    }

    /**
     * Tpl:magalogueEntriesList template element
     *
     * @param   ArrayObject<string, string> $attr   The attribute[type] $attr
     * @return  string                              rendered element
     */
    public static function magalogueEntriesList(ArrayObject $attr): string
    {
        $tpl_path   = My::path() . '/tpl/';;
        $entries_list_types = ['selected', 'first-level-categories', 'categories', 'recent'];

        // Get all _home-entries-*.html in tpl folder of theme
        $list_types_templates = Files::scandir($tpl_path);
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
        'switch (' . self::class . '::magalogueEntriesListHelper(\'list-' . $default . '\')) {' . "\n";

        foreach ($entries_list_types as $v) {
            $ret .= '   case \'' . $v . '\':' . "\n" .
            '?>' . "\n" .
            App::frontend()->template()->includeFile(['src' => '_home-entries-' . $v . '.html']) . "\n" .
                '<?php ' . "\n" .
                '       break;' . "\n";
        }

        $ret .= '}' . "\n" .
            '?>';
        return $ret;
    }

    /**
     * Tpl:magalogueSliderContent template element
     *
     * @param   ArrayObject<string, string> $attr
     * @return  string
     */
    public static function magalogueSliderContent(ArrayObject $attr): string
    {
        $tpl_path   = My::path() . '/tpl/';
        $slider_list_types = ['selected', 'first-level-categories', 'categories', 'recent'];

        // Get all _home-slider-*.html in tpl folder of theme
        $list_types_templates = Files::scandir($tpl_path);
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
        'switch (' . self::class . '::magalogueEntriesListHelper(\'slider-' . $default . '\')) {' . "\n";

        foreach ($slider_list_types as $v) {
            $ret .= '   case \'' . $v . '\':' . "\n" .
            '?>' . "\n" .
            App::frontend()->template()->includeFile(['src' => '_home-slider-' . $v . '.html']) . "\n" .
                '<?php ' . "\n" .
                '       break;' . "\n";
        }

        $ret .= '}' . "\n" .
            '?>';
        return $ret;
    }

    /**
     * Helper for Tpl:magalogueEntriesList
     *
     * @param   string  $default    The default
     * @return  string
     */
    public static function magalogueEntriesListHelper(string $default): string
    {
        $s = App::blog()->settings()->themes->get(App::blog()->settings()->system->theme . '_entries_lists');
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

    /**
     * Tpl:magalogueSocialLinks template element
     *
     * @param   ArrayObject $attr
     * @return  string      <ul> with links to social media profiles
     */
    public static function magalogueSocialLinks(): string
    {
        # Social media links
        $res     = '';
        $default = false;
        $img_url_link = My::fileURL('/img/links/');

        $s = App::blog()->settings()->themes->get(App::blog()->settings()->system->theme . '_stickers');

        if ($s === null) {
            $default = true;
        } else {
            $s = @unserialize($s);
            if (!is_array($s)) {
                $default = true;
            } else {
                $s = array_filter($s, self::class . '::cleanSocialLinks');
                if (count($s) == 0) {
                    $default = true;
                } else {
                    $count = 1;
                    foreach ($s as $sticker) {
                        $res .= self::setSocialLink($count, ($count == count($s)), $sticker['label'], $sticker['url'], $img_url_link . $sticker['image']);
                        $count++;
                    }
                }
            }
        }

        if ($default || $res == '') {
            $res = self::setSocialLink(1, true, __('Subscribe'), App::blog()->url .
                App::url()->getURLFor('feed', 'atom'), $img_url_link . 'rss.svg');
        }

        if ($res != '') {
            $res = '<ul id="stickers">' . "\n" . $res . '</ul>' . "\n";
            return $res;
        }
    }

    /**
     * Creates html list contents for Tpl:magalogueSocialLinks
     *
     * @param int $position
     * @param boolean $last
     * @param string $label
     * @param string $url
     * @param string $image
     * @return string
     */
    protected static function setSocialLink(int $position, bool $last, string $label, string $url, string $image): string
    {
        return '<li id="slink' . $position . '"' . ($last ? ' class="last"' : '') . '>' . "\n" .
            '<a href="' . $url . '">' . "\n" .
            '<img alt="' . $label . '" src="' . $image . '" title="' . $label . '" class="social-link-sticker" />' . "\n" .
            '</a>' . "\n" .
            '</li>' . "\n";
    }

    /**
     * Do not display undefined links in Tpl:magalogueSocialLinks
     *
     * @param array $s
     * @return boolean
     */
    protected static function cleanSocialLinks(array $s): bool
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

    /**
     * Tpl:magalogueBanner template element
     *
     * @return string
     */
    public static function magalogueBanner(): string
    {
        return '<?php echo ' . self::class . '::magalogueBannerHelper(); ?>';
    }

    /**
     * Helper for Tpl:magalogueBanner
     *
     * @return string
     */
    public static function magalogueBannerHelper(): string
    {
        $blog_name = App::blog()->name;
        $img_url = App::blog()->settings()->system->themes_url . '/' . App::blog()->settings()->system->theme . '/img/MagalogueBanner.png';

        $s = App::blog()->settings()->themes->get(App::blog()->settings()->system->theme . '_style');
        if ($s === null) {
            // no settings yet, return default logo
            return '<img src="' . $img_url .'" alt="' . $blog_name . '" />';
        }
        $s = @unserialize($s);
        if (!is_array($s)) {
            // settings error, return default logo
            return '<img src="' . $img_url .'" alt="' . $blog_name . '" />';
        }

        if (!empty($s['no_logo'])) {
            // no_logo has been checked, return BlogName
            return $blog_name;
        }

        if (isset($s['logo_src'])) {
            if ($s['logo_src'] !== null) {
                if ($s['logo_src'] != '') {
                    if ((substr($s['logo_src'], 0, 1) == '/') || (parse_url($s['logo_src'], PHP_URL_SCHEME) != '')) {
                        // absolute URL
                        $img_url = $s['logo_src'];
                    } else {
                        // relative URL (base = img folder of magalogue theme)
                        $img_url = App::blog()->settings()->system->themes_url . '/' . App::blog()->settings()->system->theme . '/img/' . $s['logo_src'];
                    }
                }
            }
        }

        return '<img src="' . $img_url .'" alt="' . $blog_name . '" />';
    }

    /**
     * Get current posts related entries for Tpl:magalogueRelatedEntries
     *
     * @param int $id
     * @return string
     */
    public static function thisPostrelatedEntries (int $id): string
    {
        $meta = App::meta();
        $params['post_id'] = $id;
        $params['no_content'] = false;
        $params['post_type'] = array('post');

        $rs = App::blog()->getPosts($params);
        return $meta->getMetaStr($rs->post_meta,'relatedEntries');
    }

    /**
     * Tpl:magalogueRelatedEntries template block - Displays relatedEntries in a grid
     *
     * @param ArrayObject $attr
     * @param string $content
     * @return string
     */
    public static function magalogueRelatedEntries(ArrayObject $attr, string $content): string
    {
        # Settings

        $res = '';
        if (App::plugins()->moduleExists('relatedEntries')) {
            if (App::url()->type == 'post')
            {

                $s = &App::blog()->settings()->relatedEntries;

                if (!$s->relatedEntries_enabled) {
                    return '';
                }

                $lastn = -1;
                if (isset($attr['lastn'])) {
                    $lastn = abs((int) $attr['lastn']) + 0;
                }

                $rel = "if (" . self::class . "::thisPostrelatedEntries(App::frontend()->context()->posts->post_id) !== '') :\n";
                $rel .= "\$meta = App::meta();\n";
                $rel .= "\$r_ids = " . self::class . "::thisPostrelatedEntries(App::frontend()->context()->posts->post_id);";

                $p = "\$params['post_id'] = \$meta->splitMetaValues(\$r_ids);\n";

                if ($lastn != 0) {
                    // Set limit (aka nb of entries needed)
                    if ($lastn > 0) {
                        // nb of entries per page specified in template -> regular pagination
                        $p .= "\$params['limit'] = " . $lastn . ";\n";
                        $p .= "\$nb_entry_first_page = \$nb_entry_per_page = " . $lastn . ";\n";
                    } else {
                        // nb of entries per page not specified -> use ctx settings
                        $p .= "\$nb_entry_first_page = App::frontend()->context()->nb_entry_first_page; \$nb_entry_per_page = App::frontend()->context()->nb_entry_per_page;\n";
                        $p .= "if ((App::url()->type == 'default') || (App::url()->type == 'default-page')) {\n";
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
                $res .= App::behavior()->callBehavior("templatePrepareParams",
                    ["tag" => "Entries", "method" => "blog::getPosts"],
                    $attr, $content);
                $res .= 'App::frontend()->context()->post_params = $params;' . "\n";
                $res .= 'App::frontend()->context()->posts = App::blog()->getPosts($params); unset($params);' . "\n";
                $res .= "?>\n";
                $res .=
                    '<?php while (App::frontend()->context()->posts->fetch()) : ?>' . $content . '<?php endwhile; ' .
                    'App::frontend()->context()->posts = null; App::frontend()->context()->post_params = null;
                    endif; ?>';

            }
        }
        return $res;

    }
}

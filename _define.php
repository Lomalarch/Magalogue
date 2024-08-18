<?php
# -- BEGIN LICENSE BLOCK ---------------------------------------
# This file is part of Magalogue, a theme for Dotclear
#
# Copyright (c) Noé Cendrier
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK -----------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
    'Magalogue',                    // Name
    'Thème adaptatif',              // Description
    'Julien Jakoby / Noé Cendrier', // Author
    '2.1',                          // Version
    [                               // Properties
        'requires'    => [['core', '2.30']],
        'standalone_config' => true,
        'type'   => 'theme',
        'tplset' => 'dotty',
        'support'     => 'https://github.com/Lomalarch/Magalogue',
    ]
);

<?php
# -- BEGIN LICENSE BLOCK ---------------------------------------
# This file is part of Berlin, a theme for Dotclear
#
# Copyright (c) Association Dotclear
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK -----------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */				"Magalogue",
	/* Description*/		"Thème adaptatif",
	/* Author */			"Julien Jakoby / Noé Cendrier",
	/* Version */			'1',
	/* Properties */		array(
								'type' => 'theme',
								'tplset' => 'dotty'
							)
);

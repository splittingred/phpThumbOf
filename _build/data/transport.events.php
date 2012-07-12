<?php
/**
 * phpThumbOf
 *
 * Copyright 2009-2012 by Shaun McCormick <shaun@modx.com>
 *
 * phpThumbOf is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * phpThumbOf is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * phpThumbOf; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package phpthumbof
 */
/**
 * @package phpthumbof
 * @subpackage build
 */
$events = array();

$events[1]= $modx->newObject('modEvent');
$events[1]->fromArray(array(
    'id' => 1,
    'event' => 'OnPhpThumbOfInit',
    'service' => 1,
    'groupname' => 'phpThumbOf',
));

return $events;
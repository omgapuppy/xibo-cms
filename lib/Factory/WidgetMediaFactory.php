<?php
/*
 * Xibo - Digital Signage - http://www.xibo.org.uk
 * Copyright (C) 2015 Spring Signage Ltd
 *
 * This file (WidgetMediaFactory.php) is part of Xibo.
 *
 * Xibo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Xibo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Xibo.  If not, see <http://www.gnu.org/licenses/>.
 */


namespace Xibo\Factory;


class WidgetMediaFactory extends BaseFactory
{
    /**
     * Media Linked to Widgets by WidgetId
     * @param int $widgetId
     * @return array[int]
     */
    public static function getByWidgetId($widgetId)
    {
        return WidgetMediaFactory::query(null, array('widgetId' => $widgetId));
    }

    /**
     * Query Media Linked to Widgets
     * @param array $sortOrder
     * @param array $filterBy
     * @return array[int]
     */
    public static function query($sortOrder = null, $filterBy = null)
    {
        $sql = 'SELECT mediaId FROM `lkwidgetmedia` WHERE widgetId = :widgetId';

        return array_map(function($element) { return $element['mediaId']; }, \Xibo\Storage\PDOConnect::select($sql, array('widgetId' => \Xibo\Helper\Sanitize::getInt('widgetId', $filterBy))));
    }
}
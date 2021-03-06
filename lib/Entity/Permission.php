<?php
/*
 * Xibo - Digital Signage - http://www.xibo.org.uk
 * Copyright (C) 2015 Spring Signage Ltd
 *
 * This file (Permission.php) is part of Xibo.
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


namespace Xibo\Entity;
use Xibo\Helper\Log;

/**
 * Class Permission
 * @package Xibo\Entity
 *
 * @SWG\Definition()
 */
class Permission
{
    /**
     * @SWG\Property(description="The ID of this Permission Record")
     * @var int
     */
    public $permissionId;

    /**
     * @SWG\Property(description="The Entity ID that this Permission refers to")
     * @var int
     */
    public $entityId;

    /**
     * @SWG\Property(description="The User Group ID that this permission refers to")
     * @var int
     */
    public $groupId;

    /**
     * @SWG\Property(description="The object ID that this permission refers to")
     * @var int
     */
    public $objectId;

    /**
     * @SWG\Property(description="A flag indicating whether the groupId refers to a user specific group")
     * @var int
     */
    public $isUser;

    /**
     * @SWG\Property(description="The entity name that this refers to")
     * @var string
     */
    public $entity;

    /**
     * @SWG\Property(description="Legacy for when the Object ID is a string")
     * @var string
     */
    public $objectIdString;

    /**
     * @SWG\Property(description="The group name that this refers to")
     * @var string
     */
    public $group;

    /**
     * @SWG\Property(description="A flag indicating whether view permission is granted")
     * @var int
     */
    public $view;

    /**
     * @SWG\Property(description="A flag indicating whether edit permission is granted")
     * @var int
     */
    public $edit;

    /**
     * @SWG\Property(description="A flag indicating whether delete permission is granted")
     * @var int
     */
    public $delete;

    /**
     * @SWG\Property(description="A flag indicating whether modify permission permission is granted.")
     * @var int
     */
    public $modifyPermissions;

    public function __clone()
    {
        $this->permissionId = null;
    }

    public function save()
    {
        if ($this->permissionId == 0) {
            // Check there is something to add
            if ($this->view != 0 || $this->edit != 0 || $this->delete != 0) {
                Log::debug('Adding Permission for %s, %d. GroupId: %d - View = %d, Edit = %d, Delete = %d', $this->entity, $this->objectId, $this->groupId, $this->view, $this->edit, $this->delete);
                $this->add();
            }
        } else {
            Log::debug('Editing Permission for %s, %d. GroupId: %d - View = %d, Edit = %d, Delete = %d', $this->entity, $this->objectId, $this->groupId, $this->view, $this->edit, $this->delete);
            // Are we all 0 permissions
            if ($this->view == 0 && $this->edit == 0 && $this->delete == 0)
                $this->delete();
            else
                $this->update();
        }
    }

    private function add()
    {
        $this->permissionId = \Xibo\Storage\PDOConnect::insert('INSERT INTO `permission` (`entityId`, `groupId`, `objectId`, `view`, `edit`, `delete`) VALUES (:entityId, :groupId, :objectId, :view, :edit, :delete)', array(
            'entityId' => $this->entityId,
            'objectId' => $this->objectId,
            'groupId' => $this->groupId,
            'view' => $this->view,
            'edit' => $this->edit,
            'delete' => $this->delete,
        ));
    }

    private function update()
    {
        \Xibo\Storage\PDOConnect::update('UPDATE `permission` SET `view` = :view, `edit` = :edit, `delete` = :delete WHERE `entityId` = :entityId AND `groupId` = :groupId AND `objectId` = :objectId', array(
            'entityId' => $this->entityId,
            'objectId' => $this->objectId,
            'groupId' => $this->groupId,
            'view' => $this->view,
            'edit' => $this->edit,
            'delete' => $this->delete,
        ));
    }

    public function delete()
    {
        Log::debug('Deleting Permission for %s, %d', $this->entity, $this->objectId);
        \Xibo\Storage\PDOConnect::update('DELETE FROM `permission` WHERE entityId = :entityId AND objectId = :objectId AND groupId = :groupId', array(
            'entityId' => $this->entityId,
            'objectId' => $this->objectId,
            'groupId' => $this->groupId
        ));
    }

    public function deleteAll()
    {
        \Xibo\Storage\PDOConnect::update('DELETE FROM `permission` WHERE entityId = :entityId AND objectId = :objectId', array(
            'entityId' => $this->entityId,
            'objectId' => $this->objectId,
        ));
    }
}
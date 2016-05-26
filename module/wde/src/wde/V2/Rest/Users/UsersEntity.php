<?php
namespace wde\V2\Rest\Users;

use ArrayObject;

class UsersEntity extends ArrayObject
{
    public function exchangeArray($input) {
        /* Never send the password. */
        unset($input['pw']);
        return parent::exchangeArray($input);
    }
}

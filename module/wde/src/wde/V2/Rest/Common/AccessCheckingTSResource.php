<?php

namespace wde\V2\Rest\Common;

use wde\V2\Rest\Common\TableSwitchingResource;
use ZF\ApiProblem\ApiProblem;

class AccessCheckingTSResource extends TableSwitchingResource {       
    /** @var array  */
    protected $tablesWithAuth;
    
    /** @var string */
    protected $mainTableName;
    
    /**
     * @return boolean Whether the user is an admin user.
     */
    protected function isAdmin() {
        $this->initTableNameAndTablesWithAuth();
        return (in_array($this->mainTableName, $this->tablesWithAuth) &&
                ($this->getIdentity()->getAuthenticationIdentity()[$this->mainTableName]['write'] === true) &&
                ($this->getIdentity()->getAuthenticationIdentity()[$this->mainTableName]['writeown'] === false));
    }   
    /**
     * @return boolean Whether the user has the right to write.
     */
    protected function hasRightToWrite() {
        $this->initTableNameAndTablesWithAuth();
        return (in_array($this->mainTableName, $this->tablesWithAuth) &&
                ($this->getIdentity()->getAuthenticationIdentity()[$this->mainTableName]['write'] === true));
    }    
    /**
     * @return boolean Whether the user has the right to write.
     */
    protected function hasRightToRead() {
        $this->initTableNameAndTablesWithAuth();
        return (in_array($this->mainTableName, $this->tablesWithAuth) &&
                ($this->getIdentity()->getAuthenticationIdentity()[$this->mainTableName]['read'] === true));
    }
    
    protected function switchToTableInRouteIfExistsAndUserAuthorized() {
        $this->initTableNameAndTablesWithAuth();
        $this->realTableName = $this->mainTableName .
                               $this->realTableNameExtension;
        if ($this->mainTableName === 'dict_users') {
            return new ApiProblem(404, 'Item not found');
        }
        if ($this->hasRightToRead()) {
            $this->switchTable();
            return false;
        }        
        return new ApiProblem(403, 'Not allowed. You are not authorized for this dictionary.');
    }
    
    protected function initTableNameAndTablesWithAuth() {
        $this->mainTableName = $this->event->getRouteParam('dict_name');        
        $this->tablesWithAuth = array_keys($this->getIdentity()->getAuthenticationIdentity());        
    }
    
    protected function checkHasNoRightToWrite() {     
        if ($this->hasRightToWrite()) {
            return false;
        }        
        return new ApiProblem(403, 'Not allowed. You are not authorized to write to this dictionary.'); 
    }
    
    protected function checkHasNoAdminRights() {     
        if ($this->isAdmin()) {
            return false;
        }        
        return new ApiProblem(403, 'Not allowed. You have to be an administrator to do this.');       
    }
    
    public function fetchAll($data = array()) {          
        if (true == $trySwitchFailed = $this->switchToTableInRouteIfExistsAndUserAuthorized()) { return $trySwitchFailed; } // is an ApiProblem
        return parent::fetchAll($data);
    }
    
    public function fetch($id) {          
        if (true == $trySwitchFailed = $this->switchToTableInRouteIfExistsAndUserAuthorized()) { return $trySwitchFailed; } // is an ApiProblem
        return parent::fetch($id);
    }

    public function create($data) {          
        if (true == $trySwitchFailed = $this->switchToTableInRouteIfExistsAndUserAuthorized()) { return $trySwitchFailed; } // is an ApiProblem
        if (true == $canNotWrite = $this->checkHasNoRightToWrite()) { return $canNotWrite; } // is an ApiProblem
        return parent::create($data);
    }

    public function update($id, $data) {          
        if (true == $trySwitchFailed = $this->switchToTableInRouteIfExistsAndUserAuthorized()) { return $trySwitchFailed; } // is an ApiProblem
        if (true == $canNotWrite = $this->checkHasNoRightToWrite()) { return $canNotWrite; } // is an ApiProblem
        return parent::update($id, $data);
    }
    
    public function patch($id, $data) {          
        if (true == $trySwitchFailed = $this->switchToTableInRouteIfExistsAndUserAuthorized()) { return $trySwitchFailed; } // is an ApiProblem
        if (true == $canNotWrite = $this->checkHasNoRightToWrite()) { return $canNotWrite; } // is an ApiProblem
        return parent::patch($id, $data);
    }
    
    public function patchList($data) {
        if (true == $trySwitchFailed = $this->switchToTableInRouteIfExistsAndUserAuthorized()) { return $trySwitchFailed; } // is an ApiProblem
        if (true == $canNotWrite = $this->checkHasNoRightToWrite()) { return $canNotWrite; } // is an ApiProblem
        return parent::patchList($data);
    }

    public function delete($id) {          
        if (true == $trySwitchFailed = $this->switchToTableInRouteIfExistsAndUserAuthorized()) { return $trySwitchFailed; } // is an ApiProblem
        if (true == $isNoAdmin = $this->checkHasNoAdminRights()) { return $isNoAdmin; } // is an ApiProblem
        return parent::delete($id);
    }
    
    public function deleteList($data) {          
        if (true == $trySwitchFailed = $this->switchToTableInRouteIfExistsAndUserAuthorized()) { return $trySwitchFailed; } // is an ApiProblem
        if (true == $isNoAdmin = $this->checkHasNoAdminRights()) { return $isNoAdmin; } // is an ApiProblem
        return parent::deleteList($data);
    }
}
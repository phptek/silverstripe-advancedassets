<?php
/**
 *
 * {@link SecuredFilesystem::sync_secured()} overwrite {@link Filesystem::sync()} in a way that a folder
 * syncs its children safely, i.e, don't sync any secured child folder when the folder is non-secured,
 * and vise-versa.
 * 
 * @author Deviate Ltd 2014-2015 http://www.deviate.net.nz
 * @package silverstripe-advancedassets
 * @see {@link FolderSecured::securedSyncChildren()}
 * @todo Modify show_access_message() to show messages within the CMS.
 */
class SecuredFilesystem extends Filesystem {
    
    /**
     * 
     * @param number $folderID
     * @return string
     */
    public static function sync_secured($folderID = null) {
        $folder = DataObject::get_by_id('Folder', (int) $folderID);
        if(!($folder && $folder->exists())) {
            $folder = singleton('Folder');
        }
        
        $results = $folder->securedSyncChildren();
        $finished = false;
        while(!$finished) {
            $orphans = DB::query("SELECT \"C\".\"ID\" FROM \"File\" AS \"C\"
				LEFT JOIN \"File\" AS \"P\" ON \"C\".\"ParentID\" = \"P\".\"ID\"
				WHERE \"P\".\"ID\" IS NULL AND \"C\".\"ParentID\" > 0");
            $finished = true;
            if($orphans) foreach($orphans as $orphan) {
                $finished = false;
                // Delete the database record but leave the filesystem alone
                $file = DataObject::get_by_id("File", $orphan['ID']);
                $file->deleteDatabaseOnly();
                unset($file);
            }
        }
        
        return _t(
            'Filesystem.SYNCRESULTS',
            'Sync complete: {createdcount} items created, {deletedcount} items deleted',
            array('createdcount' => (int)$results['added'], 'deletedcount' => (int)$results['deleted'])
        );
    }
    
    /**
     * 
     * Adapted and simplified from {@link Security::permissionFailure()}.
     * 
     * @param Controller $controller
     * @param string $message
     * @return SS_HTTPResponse $response
     */
    public static function show_access_message($controller, $message = '') {
        $response = $controller->getResponse();
        $response->setBody($message);
        $response->setStatusDescription($message);
        $response->setStatusCode(403);
        return $response;
    }
    
    /**
     * Utility static to avoid repetition.
     * 
     * @param Controller $controller
     * @param string $identifier e.g. 'ParentID' or 'ID'
     * @retun number
     */
    public static function get_numeric_identifier(Controller $controller, $identifier = 'ID') {
        $params = $controller->getRequest()->getVars();
        $useId = function() use($controller, $params, $identifier) {
            if(!isset($params[$identifier])) {
                if(!isset($controller->urlParams[$identifier])) {
                    return 0;
                }
                return $controller->urlParams[$identifier];
            }
            return $params[$identifier];
        };
        
        $id = $useId();
        return (int) !empty($id) && is_numeric($id) ? $id : 0;
    }
}

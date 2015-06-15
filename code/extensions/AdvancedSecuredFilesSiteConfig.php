<?php
/**
 * 
 * @author Deviate Ltd 2014-2015 http://www.deviate.net.nz
 * @package silverstripe-advancedassets
 */
class AdvancedSecuredFilesSiteConfig extends DataExtension {
    
    /**
     *
     * @var array
     */
    private static $db = array(
        "SecuredFileDefaultTitle"   => "Varchar",
        "SecuredFileDefaultContent" => "HTMLText",
    );
    
    /**
     *
     * @var array
     */
    private static $has_one = array(
        "LockpadImageNeedLogIn"     => "Image",
        "LockpadImageNoAccess"      => "Image",
        "LockpadImageNoLongerAvailable" => "Image",
        "LockpadImageNotYetAvailable"   => "Image",
    );
    
    /**
     * 
     * @var array
     */
    private static $allowed_components = array(
        'security',
        'embargoexpiry',
        'metadata'
    );
    
    /**
     * 
     * @param string $component
     * @throws AdvancedAssetsException
     * @return boolean
     */
    private static function is_component_enabled($component) {
        $component = strtolower(trim($component));
        if(!in_Array($component, self::$allowed_components)) {
            throw new AdvancedAssetsException('Component not allowed.');
        }
        
        $componentKey = 'component_' . $component . '_enabled';
        return Config::inst()->get('AdvancedSecuredFilesSiteConfig', $component);
    }
    
    /**
     * 
     * @return boolean
     */
    public function is_security_enabled() {
        return self::is_component_enabled('security');
    }
    
    /**
     * 
     * @return boolean
     */
    public function is_metadata_enabled() {
        return self::is_component_enabled('metadata');
    }
    
    /**
     * 
     * @return boolean
     */
    public function is_embargoexpiry_enabled() {
        return self::is_component_enabled('embargoexpiry');
    }

    /**
     * 
     * @param FieldList $fields
     * @return void
     */
    public function updateCMSFields(FieldList $fields){
        $fields->addFieldsToTab("Root.SecuredFiles", array(
            UploadField::create('LockpadImageNeedLogIn', 'Lockpad image that shows "need to login"')
                ->setDescription("Image that shows as default when a image is required to login to view")
                ->setAllowedMaxFileNumber(1)
                ->setAllowedFileCategories("image"),
            UploadField::create('LockpadImageNoAccess', 'Lockpad image that shows "have no access"')
                ->setDescription("Image that shows as default when a image is not viewable by current user")
                ->setAllowedMaxFileNumber(1)
                ->setAllowedFileCategories("image"),
            UploadField::create('LockpadImageNoLongerAvailable', 'Lockpad image that shows "No longer available"')
                ->setDescription("Image that shows as default when a image is expired")
                ->setAllowedMaxFileNumber(1)
                ->setAllowedFileCategories("image"),
            UploadField::create('LockpadImageNotYetAvailable', 'Lockpad image that shows "Not yet available"')
                ->setDescription("Image that shows as default when a image is embargoed")
                ->setAllowedMaxFileNumber(1)
                ->setAllowedFileCategories("image"),
            TextField::create('SecuredFileDefaultTitle', "Title that shows as page title")
                ->setDescription("Title that shows as page title in a generated page for an locked document"),
            HtmlEditorField::create('SecuredFileDefaultContent', "Content that shows as page content")
                ->setDescription("Content that shows as page content in a generated page for an locked document"),
        ));
    }
}

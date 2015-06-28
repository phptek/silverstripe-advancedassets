<?php
/**
 * 
 * @author Deviate Ltd 2014-2015 http://www.deviate.net.nz
 * @package silverstripe-advancedassets
 */
class AdvancedAssetsFilesSiteConfig extends DataExtension {

    /**
     *
     * @var array
     */
    private static $db = array(
        "SecuredFileDefaultTitle" => "Varchar",
        "SecuredFileDefaultContent" => "HTMLText",
    );

    /**
     *
     * @var array
     */
    private static $has_one = array(
        "LockpadImageNeedLogIn" => "Image",
        "LockpadImageNoAccess" => "Image",
        "LockpadImageNoLongerAvailable" => "Image",
        "LockpadImageNotYetAvailable" => "Image",
    );

    /**
     * 
     * The module is comprised of advanced "components". This static provides us
     * with an authoratative list of them.
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
     * Determine if a component is enabled or not. By default all components are disabled
     * unless explicitly enabled by a developer in a project's YML config as follows:
     * 
     *    AdvancedAssetsFilesSiteConfig:
     *      component_security_enabled: true
     *      component_embargoexpiry_enabled: true
     *      component_metadata_enabled: true
     * 
     * @param string $component
     * @throws AdvancedAssetsException
     * @return boolean
     */
    private static function is_component_enabled($component) {
        $component = strtolower(trim($component));
        if(!in_array($component, self::$allowed_components)) {
            throw new AdvancedAssetsException('Component not allowed.');
        }

        $componentKey = 'component_' . $component . '_enabled';
        $setting = Config::inst()->get('AdvancedAssetsFilesSiteConfig', $componentKey);
        if($setting !== null) {
            return (bool) $setting;
        }

        return false;
    }

    /**
     * 
     * @return boolean
     */
    public static function is_security_enabled() {
        return self::is_component_enabled('security');
    }

    /**
     * 
     * @return boolean
     */
    public static function is_metadata_enabled() {
        return self::is_component_enabled('metadata');
    }

    /**
     * 
     * @return boolean
     */
    public static function is_embargoexpiry_enabled() {
        return self::is_component_enabled('embargoexpiry');
    }

    /**
     * 
     * Generate an icon with appropriate CSS styling for the CMS for the given 
     * module "component".
     * 
     * @param string $component
     * @return string
     */
    public static function component_cms_icon($component) {
        $enabled = (self::is_component_enabled($component) ? 'en' : 'dis') . 'abled';
        $title = ucfirst($component) . ' component ' . $enabled . '.';
        return '<span class="component-icon '
            . $component . ' '
            . $enabled . '" title="' . $title
            . '">&nbsp;</span>';
    }

    /**
     * 
     * @param FieldList $fields
     * @return void
     */
    public function updateCMSFields(FieldList $fields) {
        $fields->addFieldsToTab("Root.AdvancedAssets", array(
            UploadField::create('LockpadImageNeedLogIn', 'Icon for: "Need to login"')
            ->setDescription("This image is shown by default when a user requests a file that requires a login to view.")
            ->setAllowedMaxFileNumber(1)
            ->setAllowedFileCategories("image"),
            UploadField::create('LockpadImageNoAccess', 'Icon for: "Have no access"')
            ->setDescription("This image is shown by default when a user requests a file that is not viewable by the current user.")
            ->setAllowedMaxFileNumber(1)
            ->setAllowedFileCategories("image"),
            UploadField::create('LockpadImageNoLongerAvailable', 'Icon for: "No longer available"')
            ->setDescription("This image is shown by default when a user requests a file that has expired.")
            ->setAllowedMaxFileNumber(1)
            ->setAllowedFileCategories("image"),
            UploadField::create('LockpadImageNotYetAvailable', 'Icon for: "Not yet available"')
            ->setDescription("This image is shown by default when a user requests a file that is embargoed.")
            ->setAllowedMaxFileNumber(1)
            ->setAllowedFileCategories("image"),
            TextField::create('SecuredFileDefaultTitle', "Title that shows as page title")
            ->setDescription("Title that shows as page title in a generated page for a locked document"),
            $content = HtmlEditorField::create('SecuredFileDefaultContent', "Content that shows as page content")
            ->setDescription("Content that shows as page content in a generated page for a locked document")
        ));
        
        $content->setRows(10);
    }

}

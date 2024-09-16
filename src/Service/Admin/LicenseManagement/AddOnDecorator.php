<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

class AddOnDecorator
{
    private $addOn;

    /**
     * @param object $addOn
     *
     * @throws \Exception
     */
    public function __construct($addOn)
    {
        if (empty($addOn)) {
            throw new \Exception(sprintf(esc_html__('Add-on is empty.', 'wp-statistics')));
        }

        $this->addOn = $addOn;
    }

    /**
     * Returns add-on object.
     *
     * @return object
     */
    public function getAddOnObject()
    {
        return $this->addOn;
    }

    /**
     * Returns add-on ID.
     *
     * @return int
     */
    public function getId()
    {
        return intval($this->getAddOnObject()->id);
    }

    /**
     * Returns add-on name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getAddOnObject()->name;
    }

    /**
     * Returns add-on URL.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getAddOnObject()->url;
    }

    /**
     * Returns add-on description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getAddOnObject()->description;
    }

    /**
     * Returns add-on icon URL.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->getAddOnObject()->icon;
    }

    /**
     * Returns add-on version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->getAddOnObject()->version;
    }

    /**
     * Returns add-on price.
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->getAddOnObject()->price;
    }

    /**
     * Whether this add-on is featured or not?
     *
     * @return bool
     */
    public function IsAddOnFeatured()
    {
        return $this->getAddOnObject()->is_feature == true ? true : false;
    }

    /**
     * Returns add-on price.
     *
     * @return string
     */
    public function getFeaturedLabel()
    {
        return $this->getAddOnObject()->featured_label;
    }
}

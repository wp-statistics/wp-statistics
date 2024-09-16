<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

class AddOnsListDecorator
{
    /**
     * Add-ons array.
     *
     * @var array Format: `['{SLUG}' => {ADDON-OBJECT}, '{SLUG}' => {ADDON-OBJECT}, ...]`.
     */
    private $addOns;

    /**
     * @param array $addOns
     *
     * @throws \Exception
     */
    public function __construct($addOns)
    {
        if (empty($addOns) || !is_array($addOns)) {
            // translators: %s: Add-ons list request's result.
            throw new \Exception(sprintf(esc_html__('Invalid add-ons list result: %s', 'wp-statistics'), esc_html(var_export($addOns, true))));
        }

        // Fill `addOns` attribute array
        $this->addOns = [];
        foreach ($addOns as $addOn) {
            if (empty($addOn->id) || empty($addOn->slug)) {
                continue;
            }

            $this->addOns[$addOn->slug] = $addOn;
        }
    }

    /**
     * Returns the full list of add-ons.
     *
     * @return array
     */
    public function getAddOnsList()
    {
        return $this->addOns;
    }

    /**
     * Returns an add-on by its slug.
     *
     * @param string $slug
     *
     * @return object
     *
     * @throws \Exception
     */
    public function getAddOnObject($slug)
    {
        if (empty($this->addOns[$slug])) {
            // translators: %s: Add-on slug.
            throw new \Exception(sprintf(esc_html__('No add-on found with the given slug: %s', 'wp-statistics'), esc_html($slug)));
        }

        return $this->addOns[$slug];
    }

    /**
     * Returns add-on ID by slug.
     *
     * @param string $slug
     *
     * @return int
     *
     * @throws \Exception
     */
    public function getAddOnId($slug)
    {
        return intval($this->getAddOnObject($slug)->id);
    }

    /**
     * Returns add-on name by slug.
     *
     * @param string $slug
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getAddOnName($slug)
    {
        return $this->getAddOnObject($slug)->name;
    }

    /**
     * Returns add-on URL by slug.
     *
     * @param string $slug
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getAddOnUrl($slug)
    {
        return $this->getAddOnObject($slug)->url;
    }

    /**
     * Returns add-on description by slug.
     *
     * @param string $slug
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getAddOnDescription($slug)
    {
        return $this->getAddOnObject($slug)->description;
    }

    /**
     * Returns add-on icon URL by slug.
     *
     * @param string $slug
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getAddOnIcon($slug)
    {
        return $this->getAddOnObject($slug)->icon;
    }

    /**
     * Returns add-on version by slug.
     *
     * @param string $slug
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getAddOnVersion($slug)
    {
        return $this->getAddOnObject($slug)->version;
    }

    /**
     * Returns add-on price by slug.
     *
     * @param string $slug
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getAddOnPrice($slug)
    {
        return $this->getAddOnObject($slug)->price;
    }

    /**
     * Whether this add-on is featured or not?
     *
     * @param string $slug
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function IsAddOnFeatured($slug)
    {
        return $this->getAddOnObject($slug)->is_feature == true ? true : false;
    }

    /**
     * Returns add-on price by slug.
     *
     * @param string $slug
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getAddOnFeaturedLabel($slug)
    {
        return $this->getAddOnObject($slug)->featured_label;
    }
}

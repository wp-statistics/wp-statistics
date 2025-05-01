<?php

namespace WP_Statistics\Decorators;

class MarketingCampaignDecorator
{
    /**
     * @var mixed The marketing campaign object being decorated.
     */
    private $marketingCampaign;

    /**
     * MarketingCampaignDecorator constructor.
     *
     * @param mixed $marketingCampaign The marketing campaign object.
     */
    public function __construct($marketingCampaign)
    {
        $this->marketingCampaign = $marketingCampaign;
    }

    /**
     * Get the marketing campaign ID.
     *
     * @return int|null The ID of the marketing campaign or null if not set.
     */
    public function getID()
    {
        return $this->marketingCampaign->id ?? null;
    }

    /**
     * Get the marketing campaign URL.
     *
     * @return string|null.
     */
    public function getUrl()
    {
        return $this->marketingCampaign->promo_banner['url'] ?? null;
    }

    /**
     * Get the marketing campaign icon.
     *
     * @return string|null
     */
    public function getIcon()
    {
        return json_decode($this->marketingCampaign->promo_banner['icon']) ?? null;
    }

    /**
     * Get the marketing campaign title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->marketingCampaign->promo_banner['title'] ?? null;
    }

    /**
     * Get the marketing campaign tooltip.
     *
     * @return string|null
     */
    public function getTooltip()
    {
        return $this->marketingCampaign->promo_banner['tooltip'] ?? null;
    }

    /**
     * Get the marketing campaign activated at.
     *
     * @return string|null
     */
    public function getActivatedAt()
    {
        return $this->marketingCampaign->promo_banner['activated_at'] ?? null;
    }

    /**
     * Get the marketing campaign expires at.
     *
     * @return string|null
     */
    public function getExpiresAt()
    {
        return $this->marketingCampaign->promo_banner['expires_at'] ?? null;
    }

    /**
     * Get the background color of the marketing campaign.
     *
     * @return string|null
     */
    public function getBackgroundColor()
    {
        $backgroundColors = [
            'inherit' => '',
            'danger'  => 'wps-marketing-campaign__danger',
            'info'    => 'wps-marketing-campaign__info',
            'warning' => 'wps-marketing-campaign__warning',
            'success' => 'wps-marketing-campaign__success'
        ];

        return $backgroundColors[$this->marketingCampaign->promo_banner['background_color']] ?? null;
    }

    /**
     * Get the text color of the marketing campaign.
     *
     * @return string|null
     */
    public function textColor()
    {
        $textColor = [
            'inherit' => '',
            'dark'    => 'wps-marketing-campaign-text__dark',
            'light'   => 'wps-marketing-campaign-text__light',
        ];

        return $textColor[$this->marketingCampaign->promo_banner['text_color']] ?? null;
    }

    /**
     * Get the modal of the marketing campaign
     */
    public function getModal()
    {
        return $this->marketingCampaign->modal ?? null;
    }

    /**
     * Get the modal of the marketing campaign
     */
    public function getSidebarCallout()
    {
        return $this->marketingCampaign->sidebar_callout ?? null;
    }
}
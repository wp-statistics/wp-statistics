<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
use WP_Statistics\Utils\Url;

class ReferralDecorator
{
    private $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     * Get the raw referrer value.
     *
     * @return string|null
     */
    public function getRawReferrer()
    {
        return $this->item->referred ?? null;
    }

    /**
     * Get the referrer url.
     *
     * @return string|null
     */
    public function getReferrer()
    {
        return $this->item->referred ? Url::formatUrl($this->item->referred) : null;
    }

    /**
     * Get the source channel name (e.g., Direct, Organic Search, etc.).
     *
     * @return string|null
     */
    public function getSourceChannel()
    {
        return SourceChannels::getName($this->getRawSourceChannel());
    }

    /**
     * Get the raw source channel value (e.g., direct, search, etc.).
     *
     * @return string|null
     */
    public function getRawSourceChannel()
    {
        return $this->item->source_channel ?? 'unassigned';
    }

    /**
     * Get the source name (e.g., Google, Yandex, etc.).
     *
     * @return string|null
     */
    public function getSourceName()
    {
        return $this->item->source_name ?? null;
    }

    /**
     * Get the source name for a visit that came without a referrer but whose source is known
     * from the landing URL (for example Microsoft Ads via msclkid). Returns null when there is a
     * referrer or the visit is direct traffic, so those rows render as before.
     *
     * @return string|null
     */
    public function getSourceNameWithoutReferrer()
    {
        if (!empty($this->item->referred) || $this->getRawSourceChannel() === 'direct') {
            return null;
        }

        return $this->getSourceName();
    }

    /**
     * Name of the row in a short list (Overview "Top Referring", WP Dashboard).
     *
     * Rows are grouped by domain and source channel, like the Referrers report, so one
     * domain can come twice (google.com as Google and as Google Ads). Pass the rows of the
     * list: a domain that is listed more than once gets its source name added, so the rows
     * never look the same. A row without a referrer gets "(no referrer)".
     *
     * @param ReferralDecorator[] $rows All rows of the list.
     * @return string
     */
    public function getListLabel($rows = [])
    {
        $domain = (string)$this->getRawReferrer();
        $source = (string)$this->getSourceName();

        if ($domain === '') {
            return $source !== ''
                /* translators: %s: Source name, for example "Google Ads". */
                ? sprintf(__('%s (no referrer)', 'wp-statistics'), $source)
                : __('(no referrer)', 'wp-statistics');
        }

        $same = 0;
        foreach ((array)$rows as $row) {
            if ($row instanceof self && (string)$row->getRawReferrer() === $domain) {
                $same++;
            }
        }

        if ($same > 1 && $source !== '') {
            /* translators: 1: Referrer domain, for example "google.com". 2: Source name, for example "Google Ads". */
            return sprintf(__('%1$s (%2$s)', 'wp-statistics'), $domain, $source);
        }

        return $domain;
    }

    /**
     * Get the date of the referral.
     *
     * @return string
     */
    public function getDate()
    {
        return $this->item->last_counter ?? null;
    }

    /**
     * Get the total number of referrals.
     *
     * @param bool $raw Whether return raw value or formatted.
     * @return int|string
     */
    public function getTotalReferrals($raw = false)
    {
        if (empty($this->item->visitors)) return 0;

        return $raw ? intval($this->item->visitors) : number_format_i18n($this->item->visitors);
    }
}
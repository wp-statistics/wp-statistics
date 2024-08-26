import { Fragment } from '@wordpress/element';

const ContentElement = ({ data }) => {
    const totalViews = data.totalViews.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    const totalVisitors = data.totalVisitors.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    const topReferrerCount = data.topReferrerCount.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    const thisPeriodViews = data.thisPeriodViews.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    const thisPeriodVisitors = data.thisPeriodVisitors.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    const thisPeriodTopReferrerCount = data.thisPeriodTopReferrerCount.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

    const topReferrer = data.topReferrer.toString().replace('www.', '').trim();
    let topReferrerLabel = topReferrer != '' && topReferrer.includes('//') ? topReferrer.substring(topReferrer.indexOf('//') + 2).trim() : topReferrer;
    topReferrerLabel = topReferrerLabel != '' && topReferrerLabel.includes('/') ? topReferrerLabel.substring(0, topReferrerLabel.indexOf('/')).trim() : topReferrerLabel;
    const thisPeriodTopReferrer = data.thisPeriodTopReferrer.toString().replace('www.', '').trim();
    let thisPeriodTopReferrerLabel = thisPeriodTopReferrer != '' && thisPeriodTopReferrer.includes('//') ? thisPeriodTopReferrer.substring(thisPeriodTopReferrer.indexOf('//') + 2).trim() : thisPeriodTopReferrer;
    thisPeriodTopReferrerLabel = thisPeriodTopReferrerLabel != '' && thisPeriodTopReferrerLabel.includes('/') ? thisPeriodTopReferrerLabel.substring(0, thisPeriodTopReferrerLabel.indexOf('/')).trim() : thisPeriodTopReferrerLabel;

    const topReferrerText = data.topReferrerCount > 0 && topReferrerLabel != '' ? (
        <Fragment>, with '<a href={topReferrer} target="_blank" rel="noreferrer nofollow">{topReferrerLabel}</a>' leading with <b>{topReferrerCount} referrals</b></Fragment>
    ) : '';
    const thisPeriodTopReferrerText = data.thisPeriodTopReferrerCount > 0 && thisPeriodTopReferrerLabel != '' ? (
        <Fragment> The top referrer domain is '<a href={thisPeriodTopReferrer} target="_blank" rel="noreferrer nofollow">{thisPeriodTopReferrerLabel}</a>' with <b>{thisPeriodTopReferrerCount} visits</b>.</Fragment>
    ) : '';

    // Display the first part of text only if the post has been published more than a week ago
    const thisPeriodText = (new Date) - Date.parse(data.publishDateString) >= (7 * 24 * 60 * 60 * 1000) ? (
        <Fragment>Over the past week (<b>{data.fromString} - {data.toString}</b>), this post has been <b>viewed {thisPeriodViews} times by {thisPeriodVisitors} visitors</b>.{thisPeriodTopReferrerText}<br /></Fragment>
    ) : '';

    return (
        <div className="wp-statistics-post-summary-panel-content">
            <p>
                {thisPeriodText}
                In total, this post has been <b>viewed {totalViews} times by {totalVisitors} visitors</b>{topReferrerText}. For more detailed insights, visit the <b><a href={data.contentAnalyticsUrl} target="_blank">analytics section</a></b>.
            </p>
        </div>
    );
};

export default ContentElement;

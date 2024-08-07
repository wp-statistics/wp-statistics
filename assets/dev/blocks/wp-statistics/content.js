import { Fragment } from '@wordpress/element';

const ContentElement = ({ data }) => {
    const topReferrer = data.topReferrer.toString().replace('www.', '').trim();
    let topReferrerLabel = topReferrer != '' && topReferrer.includes('//') ? topReferrer.substring(topReferrer.indexOf('//') + 2).trim() : topReferrer;
    topReferrerLabel = topReferrerLabel != '' && topReferrerLabel.includes('/') ? topReferrerLabel.substring(0, topReferrerLabel.indexOf('/')).trim() : topReferrerLabel;
    const thisWeekTopReferrer = data.thisWeekTopReferrer.toString().replace('www.', '').trim();
    let thisWeekTopReferrerLabel = thisWeekTopReferrer != '' && thisWeekTopReferrer.includes('//') ? thisWeekTopReferrer.substring(thisWeekTopReferrer.indexOf('//') + 2).trim() : thisWeekTopReferrer;
    thisWeekTopReferrerLabel = thisWeekTopReferrerLabel != '' && thisWeekTopReferrerLabel.includes('/') ? thisWeekTopReferrerLabel.substring(0, thisWeekTopReferrerLabel.indexOf('/')).trim() : thisWeekTopReferrerLabel;

    const topReferrerText = data.topReferrerCount > 0 && topReferrerLabel != '' ? (
        <Fragment>, with '<a href={topReferrer} target="_blank" rel="noreferrer nofollow">{topReferrerLabel}</a>' leading with {data.topReferrerCount} referrals</Fragment>
    ) : '';
    const thisWeekTopReferrerText = data.thisWeekTopReferrerCount > 0 && thisWeekTopReferrerLabel != '' ? (
        <Fragment> The top referrer domain is '<a href={thisWeekTopReferrer} target="_blank" rel="noreferrer nofollow">{thisWeekTopReferrerLabel}</a>' with {data.thisWeekTopReferrerCount} visits.</Fragment>
    ) : '';

    return (
        <div>
            <p>
                Over the past week ({data.fromString} - {data.toString}), this post has been viewed {data.thisWeekViews} times by {data.thisWeekVisitors} visitors.{thisWeekTopReferrerText}<br />
                In total, it has been viewed {data.totalViews} times by {data.totalVisitors} visitors{topReferrerText}. For more detailed insights, visit the <a href={data.contentAnalyticsUrl} target="_blank">analytics section</a>.
            </p>
        </div>
    );
};

export default ContentElement;

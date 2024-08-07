import { Fragment } from '@wordpress/element';

const ContentElement = ({ data }) => {
    const totalViews = data.totalViews.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    const totalVisitors = data.totalVisitors.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    const topReferrerCount = data.topReferrerCount.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    const thisWeekViews = data.thisWeekViews.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    const thisWeekVisitors = data.thisWeekVisitors.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    const thisWeekTopReferrerCount = data.thisWeekTopReferrerCount.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

    const topReferrer = data.topReferrer.toString().replace('www.', '').trim();
    let topReferrerLabel = topReferrer != '' && topReferrer.includes('//') ? topReferrer.substring(topReferrer.indexOf('//') + 2).trim() : topReferrer;
    topReferrerLabel = topReferrerLabel != '' && topReferrerLabel.includes('/') ? topReferrerLabel.substring(0, topReferrerLabel.indexOf('/')).trim() : topReferrerLabel;
    const thisWeekTopReferrer = data.thisWeekTopReferrer.toString().replace('www.', '').trim();
    let thisWeekTopReferrerLabel = thisWeekTopReferrer != '' && thisWeekTopReferrer.includes('//') ? thisWeekTopReferrer.substring(thisWeekTopReferrer.indexOf('//') + 2).trim() : thisWeekTopReferrer;
    thisWeekTopReferrerLabel = thisWeekTopReferrerLabel != '' && thisWeekTopReferrerLabel.includes('/') ? thisWeekTopReferrerLabel.substring(0, thisWeekTopReferrerLabel.indexOf('/')).trim() : thisWeekTopReferrerLabel;

    const topReferrerText = data.topReferrerCount > 0 && topReferrerLabel != '' ? (
        <Fragment>, with '<a href={topReferrer} target="_blank" rel="noreferrer nofollow">{topReferrerLabel}</a>' leading with <b>{topReferrerCount} referrals</b></Fragment>
    ) : '';
    const thisWeekTopReferrerText = data.thisWeekTopReferrerCount > 0 && thisWeekTopReferrerLabel != '' ? (
        <Fragment> The top referrer domain is '<a href={thisWeekTopReferrer} target="_blank" rel="noreferrer nofollow">{thisWeekTopReferrerLabel}</a>' with <b>{thisWeekTopReferrerCount} visits</b>.</Fragment>
    ) : '';

    return (
        <div>
            <p>
                Over the past week (<b>{data.fromString} - {data.toString}</b>), this post has been <b>viewed {thisWeekViews} times by {thisWeekVisitors} visitors</b>.{thisWeekTopReferrerText}<br />
                In total, it has been <b>viewed {totalViews} times by {totalVisitors} visitors</b>{topReferrerText}. For more detailed insights, visit the <b><a href={data.contentAnalyticsUrl} target="_blank">analytics section</a></b>.
            </p>
        </div>
    );
};

export default ContentElement;

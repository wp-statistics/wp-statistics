const ContentElement = ({ data }) => {
    return (
        <div>
            <p>
                Over the past week ({data.fromString} - {data.toString}), this post has been viewed {data.thisWeekViews} times by {data.thisWeekVisitors} visitors. The top referrer domain is '{data.thisWeekTopReferrer}' with {data.thisWeekTopReferrerCount} visits.<br />
                In total, it has been viewed {data.totalViews} times by {data.totalVisitors} visitors, with '{data.topReferrer}' leading with {data.topReferrerCount} referrals. For more detailed insights, visit the analytics section.
            </p>
        </div>
    );
};

export default ContentElement;

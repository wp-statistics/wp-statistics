import React from "react";
import { __ } from "@wordpress/i18n";
import "../../../../scss/components/_confirmation-info.scss";

const ConfirmationInfo = ({ label, detail }) => {
    return (
        <p className="confirmation-info">
            <strong>{__(label, "wp-statistics")}</strong> {__(detail, "wp-statistics")}
        </p>
    );
};

export default ConfirmationInfo; 
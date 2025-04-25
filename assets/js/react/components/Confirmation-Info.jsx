import React from "react";
import { __ } from "@wordpress/i18n";

const ConfirmationInfo = ({ label, detail }) => {
    return (
        <p style={{ fontSize: "15px", margin: "0px" }}>
            <strong>{__(label, "wp-statistics")}</strong> {__(detail, "wp-statistics")}
        </p>
    );
};

export default ConfirmationInfo;

import React from "react";
import {__} from "@wordpress/i18n";
import styles from "./styles.module.scss";

const ConfirmationInfo = ({label, detail}) => {
    return (
            <p className={`${styles.confirmationInfo} wps-wrap__confirmationInfo`}>
                <strong>{label}</strong> {detail}
            </p>
    );
};

export default ConfirmationInfo;
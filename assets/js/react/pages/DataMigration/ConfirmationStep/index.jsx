import React, {useState, useEffect} from "react";
import {__} from "@wordpress/i18n";
import {
    Card,
    CardBody,
    CardFooter,
    Button,
    Panel,
    PanelBody,
    Notice
} from "@wordpress/components";
import SelectedRadio from "../../../../../images/selected-radio.png";
import ConfirmationInfo from "../../../components/ConfirmationInfo";
import styles from "./styles.module.scss";

const ConfirmationStep = ({handleStep}) => {
    const [data, setData] = useState(null);
    const [isValid, setIsValid] = useState(true);
    const storage = localStorage.getItem("wps_migration_option");

    useEffect(() => {
        if (storage) {
            try {
                const parsedData = JSON.parse(storage);
                setData(parsedData);

                // Validate data
                if (parsedData.type === "hybrid") {
                    const days = parseInt(parsedData.days) || 90; // Default to 90 days if not specified
                    if (days < 1) {
                        setIsValid(false);
                    }
                    // Update the data with the parsed/default days value
                    setData({...parsedData, days});
                }
            } catch (e) {
                setIsValid(false);
            }
        } else {
            handleStep("step1");
        }
    }, [storage]);

    if (!data) {
        return null;
    }

    return (
            <Card className={`${styles.confirmationStep} wps-wrap-confirmationStep`}>
                <CardBody>
                    <div className={`${styles.content} wps-wrap-confirmationStep__content`}>
                        <h2 className={`${styles.heading} wps-wrap-confirmationStep__heading`}>
                            {__("Review Your Migration Settings", "wp-statistics")}
                        </h2>

                        <Card className={`${styles.selectedCard} wps-wrap-confirmationStep__selectedCard`}>
                            <CardBody className={`${styles.selectedCardBody} wps-wrap-confirmationStep__selectedCardBody`}>
                                <div className={`${styles.cardHeader} wps-wrap-confirmationStep__cardHeader`}>
                                    <h3 className={`${styles.title} wps-wrap-confirmationStep__title`}>
                                        {data.title || ''}
                                    </h3>
                                    <img
                                            src={SelectedRadio}
                                            alt="radio-select"
                                            className={`${styles.radioImage} wps-wrap-confirmationStep__radioImage`}
                                    />
                                </div>
                                <p className={`${styles.description} wps-wrap-confirmationStep__description`}>
                                    {data.description || ''}
                                </p>
                                <ul className={`${styles.list} wps-wrap-confirmationStep__list`}>
                                    <li>
                                        <strong>{__("Estimated Time:", "wp-statistics")}</strong> {data.estimatedTime || ''}
                                    </li>
                                    <li>
                                        <strong>{__("Who It's For:", "wp-statistics")}</strong> {data.whoFor || ''}
                                    </li>
                                    {data.type === "hybrid" && (
                                            <li>
                                                <strong>{__("Days of Detailed Data:", "wp-statistics")}</strong> {data.days || "0"} {__("days", "wp-statistics")}
                                            </li>
                                    )}
                                </ul>
                            </CardBody>
                        </Card>

                        <div className={`${styles.infoContainer} wps-wrap-confirmationStep__infoContainer`}>
                            <ConfirmationInfo
                                    label={__("What's Next?", "wp-statistics")}
                                    detail={__("We'll migrate all of your historical data—visitors, devices, search engines, referrers, and more—into the new database structure.", "wp-statistics")}
                            />
                            <ConfirmationInfo
                                    label={__("What's Migrated?", "wp-statistics")}
                                    detail={__("Absolutely everything from your past analytics, so you retain complete visibility into your site's historical data.", "wp-statistics")}
                            />
                            <ConfirmationInfo
                                    label={__("What's Lost?", "wp-statistics")}
                                    detail={__("Nothing! All detailed stats will be preserved.", "wp-statistics")}
                            />
                            <ConfirmationInfo
                                    label={__("Estimated Time:", "wp-statistics")}
                                    detail={__("Depending on the size of your site and server performance, it can take anywhere from minutes to a few hours.", "wp-statistics")}
                            />

                            <Panel className={`${styles.reminderContainer} wps-wrap-confirmationStep__reminderContainer`}>
                                <PanelBody>
                                    <h3 className={`${styles.reminderTitle} wps-wrap-confirmationStep__reminderTitle`}>
                                        <strong>{__("Regardless of the choice,", "wp-statistics")}</strong>
                                        {" "}
                                        {__("you could also include these reminders at the bottom of the confirmation step:", "wp-statistics")}
                                    </h3>
                                    <ul className={`${styles.reminderList} wps-wrap-confirmationStep__reminderList`}>
                                        <li className={`${styles.reminderItem} wps-wrap-confirmationStep__reminderItem`}>
                                            {__("You can pause, cancel, or restart the migration at any time.", "wp-statistics")}
                                        </li>
                                        <li className={`${styles.reminderItem} wps-wrap-confirmationStep__reminderItem`}>
                                            {__("Nothing is deleted from your old data source until the migration is fully complete.", "wp-statistics")}
                                        </li>
                                        <li className={`${styles.reminderItem} wps-wrap-confirmationStep__reminderItem`}>
                                            {__("Need more details or help?", "wp-statistics")} <Button href="#" isLink>{__("Check our Migration FAQs or contact support.", "wp-statistics")}</Button>
                                        </li>
                                    </ul>
                                </PanelBody>
                            </Panel>

                            {!isValid && (
                                    <Notice status="error" isDismissible={false} className="confirmation-step__warning">
                                        {__("There seems to be an issue with your migration settings. Please go back and ensure all options are properly configured.", "wp-statistics")}
                                    </Notice>
                            )}
                        </div>
                    </div>
                </CardBody>

                <CardFooter className={`${styles.footer} wps-wrap-confirmationStep__footer`}>
                    <div className={`${styles.footerContent} wps-wrap-confirmationStep__footerContent`}>
                        <h3 className={`${styles.readyText} wps-wrap-confirmationStep__readyText`}>
                            🚀 {__("Ready to proceed?", "wp-statistics")}
                        </h3>
                        <p className={`${styles.instructionText} wps-wrap-confirmationStep__instructionText`}>
                            {__("You can", "wp-statistics")} <span className={`${styles.boldText} wps-wrap-confirmationStep__boldText`}>{__("go back", "wp-statistics")}</span> {__("to change the number of days or pick a different migration method. Or, click", "wp-statistics")} <span className={`${styles.boldText} wps-wrap-confirmationStep__boldText`}>{__("Start Migration", "wp-statistics")}</span> {__("to begin.", "wp-statistics")}
                        </p>
                    </div>
                    <div className={`${styles.footerActions} wps-wrap-confirmationStep__footerActions`}>
                        <Button
                                isLink
                                className={`${styles.backLink} wps-wrap-confirmationStep__backLink`}
                                onClick={() => handleStep("step1")}
                        >
                            {__("< Go Back", "wp-statistics")}
                        </Button>
                        <Button
                                variant="primary"
                                className={`${styles.startButton} wps-wrap-confirmationStep__startButton`}
                                onClick={() => handleStep("step3")}
                                disabled={!isValid}
                        >
                            {__("Start Migration", "wp-statistics")}
                        </Button>
                    </div>
                </CardFooter>
            </Card>
    );
};

export default ConfirmationStep;
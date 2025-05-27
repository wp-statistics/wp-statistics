import { useState, useEffect, createElement, Fragment } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
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
import classNames from "classnames";
import "../../../../../scss/pages/data-migration/_confirmation-step.scss";

const ConfirmationStep = ({ handleStep }) => {
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
                    setData({ ...parsedData, days });
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
        <Card>
            <CardBody>
                <div className="confirmation-step__content">
                    <h2 className="confirmation-step__heading">
                        {__("Review Your Migration Settings", "wp-statistics")}
                    </h2>
                    
                    <Card className="confirmation-step__selected-card">
                        <CardBody className="confirmation-step__selected-card-body">
                            <div className="confirmation-step__card-header">
                                <h3 className="confirmation-step__title">
                                    {data.title || ''}
                                </h3>
                                <img
                                    src={SelectedRadio}
                                    alt="radio-select"
                                    className="confirmation-step__radio-image"
                                />
                            </div>
                            <p className="confirmation-step__description">
                                {data.description || ''}
                            </p>
                            <ul className="confirmation-step__list">
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

                    <div className="confirmation-step__info-container">
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

                        <Panel className="confirmation-step__reminder-container">
                            <PanelBody>
                                <h3 className="confirmation-step__reminder-title">
                                    <strong>{__("Regardless of the choice,", "wp-statistics")}</strong>
                                    {" "}
                                    {__("you could also include these reminders at the bottom of the confirmation step:", "wp-statistics")}
                                </h3>
                                <ul className="confirmation-step__reminder-list">
                                    <li className="confirmation-step__reminder-item">
                                        {__("You can pause, cancel, or restart the migration at any time.", "wp-statistics")}
                                    </li>
                                    <li className="confirmation-step__reminder-item">
                                        {__("Nothing is deleted from your old data source until the migration is fully complete.", "wp-statistics")}
                                    </li>
                                    <li className="confirmation-step__reminder-item">
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

            <CardFooter className="confirmation-step__footer">
                <div className="confirmation-step__footer-content">
                    <h3 className="confirmation-step__ready-text">
                        🚀 {__("Ready to proceed?", "wp-statistics")}
                    </h3>
                    <p className="confirmation-step__instruction-text">
                        {__("You can", "wp-statistics")} <span className="confirmation-step__bold-text">{__("go back", "wp-statistics")}</span> {__("to change the number of days or pick a different migration method. Or, click", "wp-statistics")} <span className="confirmation-step__bold-text">{__("Start Migration", "wp-statistics")}</span> {__("to begin.", "wp-statistics")}
                    </p>
                </div>
                <div className="confirmation-step__footer-actions">
                    <Button
                        isLink
                        className="confirmation-step__back-link"
                        onClick={() => handleStep("step1")}
                    >
                        {__("< Go Back", "wp-statistics")}
                    </Button>
                    <Button
                        variant="primary"
                        className="confirmation-step__start-button"
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
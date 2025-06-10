import React, { useState, useEffect } from "react";
import { Card, CardBody, CardFooter, __experimentalHeading as Heading } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import InformationIcon from "../../../../../images/information.svg";
import classNames from "classnames";
import styles from "./styles.module.scss";

const ProgressStep = ({ handleStep }) => {
    const [isCompleted, setIsCompleted] = useState(false);
    const [isPaused, setIsPaused] = useState(false);
    const [progress, setProgress] = useState(0);
    const [currentOperation, setCurrentOperation] = useState("");
    const [error, setError] = useState(null);

    useEffect(() => {
        const storage = localStorage.getItem("wps_migration_option");
        if (!storage) {
            handleStep("step1");
        }
    }, []);

    useEffect(() => {
        if (!isPaused && !isCompleted && !error) {
            const interval = setInterval(() => {
                setProgress(prev => {
                    if (prev >= 100) {
                        clearInterval(interval);
                        setIsCompleted(true);
                        return 100;
                    }
                    return prev + 2;
                });

                // Update operation text based on progress
                if (progress < 30) {
                    setCurrentOperation(__("Preparing database tables...", "wp-statistics"));
                } else if (progress < 60) {
                    setCurrentOperation(__("Migrating historical data...", "wp-statistics"));
                } else if (progress < 90) {
                    setCurrentOperation(__("Optimizing database structure...", "wp-statistics"));
                } else {
                    setCurrentOperation(__("Finalizing migration...", "wp-statistics"));
                }
            }, 1000);

            return () => clearInterval(interval);
        }
    }, [progress, isPaused, isCompleted, error]);

    const handlePause = () => {
        setIsPaused(!isPaused);
    };

    const handleCancel = () => {
        if (window.confirm(__("Are you sure you want to cancel the migration? Progress will be lost.", "wp-statistics"))) {
            handleStep("step2");
        }
    };

    const handleComplete = () => {
        localStorage.removeItem("wps_migration_option");
        // Redirect to statistics page or reload
        window.location.href = window.location.href.split("?")[0];
    };

    if (error) {
        return (
            <Card className={`${styles.progressStep} wps-wrap-progressStep`}>
                <CardBody>
                    <Heading className={`${styles.heading} wps-wrap-progressStep__heading`}>
                        {__("Migration Error", "wp-statistics")}
                    </Heading>
                    <div className={`${styles.infoBox} ${styles.infoBoxError} wps-wrap-progressStep__infoBox wps-wrap-progressStep__infoBox--error`}>
                        <div className={`${styles.infoContent} wps-wrap-progressStep__infoContent`}>
                            <img src={InformationIcon} alt="error" className={`${styles.infoIcon} wps-wrap-progressStep__infoIcon`} />
                            <div>{error}</div>
                        </div>
                    </div>
                </CardBody>
                <CardFooter>
                    <div className={`${styles.buttonContainer} wps-wrap-progressStep__buttonContainer`}>
                        <button
                            className={`${styles.cancelButton} wps-wrap-progressStep__cancelButton`}
                            onClick={() => handleStep("step2")}
                        >
                            {__("Back", "wp-statistics")}
                        </button>
                        <button
                            className={`${styles.pauseButton} wps-wrap-progressStep__pauseButton`}
                            onClick={() => {
                                setError(null);
                                setProgress(0);
                            }}
                        >
                            {__("Retry", "wp-statistics")}
                        </button>
                    </div>
                </CardFooter>
            </Card>
        );
    }

    return (
        <Card className={`${styles.progressStep} wps-wrap-progressStep`}>
            <CardBody>
                <Heading className={`${styles.heading} wps-wrap-progressStep__heading`}>
                    {isCompleted
                        ? __("Migration Complete!", "wp-statistics")
                        : __("Migration in Progress", "wp-statistics")}
                </Heading>

                <div className={`${styles.progressContainer} wps-wrap-progressStep__progressContainer`}>
                    <div className={`${styles.operationText} wps-wrap-progressStep__operationText`}>
                        {currentOperation}
                    </div>
                    <div className={`${styles.progressBar} wps-wrap-progressStep__progressBar`}>
                        <div
                            className={`${styles.progressBarFill} wps-wrap-progressStep__progressBarFill`}
                            style={{ width: `${progress}%` }}
                        />
                    </div>
                    <div className={`${styles.progressText} wps-wrap-progressStep__progressText`}>
                        {progress}%
                    </div>
                </div>
            </CardBody>

            {isCompleted ? (
                <>
                    <div className={`${styles.successMessage} wps-wrap-progressStep__successMessage`}>
                        <p>{__("Your data has been successfully migrated to the new format!", "wp-statistics")}</p>
                        <p>{__("You can now enjoy improved performance and efficiency in your statistics.", "wp-statistics")}</p>
                    </div>
                    <CardFooter>
                        <button
                            className={`${styles.completeButton} wps-wrap-progressStep__completeButton`}
                            onClick={handleComplete}
                        >
                            {__("View Statistics", "wp-statistics")}
                        </button>
                    </CardFooter>
                </>
            ) : (
                <>
                    <div className={`${styles.infoBox} wps-wrap-progressStep__infoBox`}>
                        <div className={`${styles.infoContent} wps-wrap-progressStep__infoContent`}>
                            <img src={InformationIcon} alt="info" className={`${styles.infoIcon} wps-wrap-progressStep__infoIcon`} />
                            <div>
                                {__("Please don't close your browser while the migration is in progress. This process may take several minutes depending on your database size.", "wp-statistics")}
                            </div>
                        </div>
                    </div>

                    <CardFooter className={`${styles.footer} wps-wrap-progressStep__footer`}>
                        <div className={`${styles.buttonContainer} wps-wrap-progressStep__buttonContainer`}>
                            <button
                                className={`${styles.cancelButton} wps-wrap-progressStep__cancelButton`}
                                onClick={handleCancel}
                            >
                                {__("Cancel", "wp-statistics")}
                            </button>
                            <button
                                className={classNames(
                                    `${styles.pauseButton} wps-wrap-progressStep__pauseButton`,
                                    {
                                        [`${styles.pauseButtonPaused} wps-wrap-progressStep__pauseButton--paused`]: isPaused
                                    }
                                )}
                                onClick={handlePause}
                            >
                                {isPaused ? __("Resume", "wp-statistics") : __("Pause", "wp-statistics")}
                            </button>
                        </div>
                    </CardFooter>
                </>
            )}
        </Card>
    );
};

export default ProgressStep;
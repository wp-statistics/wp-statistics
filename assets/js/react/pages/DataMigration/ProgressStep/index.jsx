import { useState, useEffect } from "@wordpress/element";
import { Card, CardBody, CardFooter, __experimentalHeading as Heading } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import InformationIcon from "../../../../../images/information.svg";
import classNames from "classnames";
import "../../../../../scss/pages/data-migration/_progress-step.scss";

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
            <Card>
                <CardBody>
                    <Heading className="progress-step__heading">
                        {__("Migration Error", "wp-statistics")}
                    </Heading>
                    <div className="progress-step__info-box progress-step__info-box--error">
                        <div className="progress-step__info-content">
                            <img src={InformationIcon} alt="error" className="progress-step__info-icon" />
                            <div>{error}</div>
                        </div>
                    </div>
                </CardBody>
                <CardFooter>
                    <div className="progress-step__button-container">
                        <button
                            className="progress-step__cancel-button"
                            onClick={() => handleStep("step2")}
                        >
                            {__("Back", "wp-statistics")}
                        </button>
                        <button
                            className="progress-step__pause-button"
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
        <Card>
            <CardBody>
                <Heading className="progress-step__heading">
                    {isCompleted
                        ? __("Migration Complete!", "wp-statistics")
                        : __("Migration in Progress", "wp-statistics")}
                </Heading>

                <div className="progress-step__progress-container">
                    <div className="progress-step__operation-text">
                        {currentOperation}
                    </div>
                    <div className="progress-step__progress-bar">
                        <div
                            className="progress-step__progress-bar-fill"
                            style={{ width: `${progress}%` }}
                        />
                    </div>
                    <div className="progress-step__progress-text">
                        {progress}%
                    </div>
                </div>
            </CardBody>

            {isCompleted ? (
                <>
                    <div className="progress-step__success-message">
                        <p>{__("Your data has been successfully migrated to the new format!", "wp-statistics")}</p>
                        <p>{__("You can now enjoy improved performance and efficiency in your statistics.", "wp-statistics")}</p>
                    </div>
                    <CardFooter>
                        <button
                            className="progress-step__complete-button"
                            onClick={handleComplete}
                        >
                            {__("View Statistics", "wp-statistics")}
                        </button>
                    </CardFooter>
                </>
            ) : (
                <>
                    <div className="progress-step__info-box">
                        <div className="progress-step__info-content">
                            <img src={InformationIcon} alt="info" className="progress-step__info-icon" />
                            <div>
                                {__("Please don't close your browser while the migration is in progress. This process may take several minutes depending on your database size.", "wp-statistics")}
                            </div>
                        </div>
                    </div>

                    <CardFooter className="progress-step__footer">
                        <div className="progress-step__button-container">
                            <button
                                className="progress-step__cancel-button"
                                onClick={handleCancel}
                            >
                                {__("Cancel", "wp-statistics")}
                            </button>
                            <button
                                className={classNames('progress-step__pause-button', {
                                    'progress-step__pause-button--paused': isPaused
                                })}
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
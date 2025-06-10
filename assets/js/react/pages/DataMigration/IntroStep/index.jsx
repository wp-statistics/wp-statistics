import React, { useState, useEffect } from "react";
import { Card, CardBody, CardFooter, __experimentalHeading as Heading } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import InfoIcon from "../../../../../images/info-icon.svg";
import MigrationCard from "../../../components/MigrationCard";
import styles from "./styles.module.scss";

const IntroStep = ({ handleStep }) => {
    const [option, setOption] = useState("");
    const [data, setData] = useState({});
    const [hybridDays, setHybridDays] = useState("0");
    const storage = localStorage.getItem("wps_migration_option");

    useEffect(() => {
        const dataParsed = JSON.parse(storage);
        if (Boolean(dataParsed)) {
            setData(dataParsed);
            setOption(dataParsed?.type);
            if (dataParsed?.type == "hybrid") {
                setHybridDays(dataParsed?.days || "0");
            }
        } else setOption("full-detailed");
    }, [storage]);

    const handleNext = () => {
        if (option == "hybrid") {
            localStorage.setItem("wps_migration_option", JSON.stringify({ ...data, days: hybridDays }));
        } else {
            localStorage.setItem("wps_migration_option", JSON.stringify(data));
        }
        handleStep("step2");
    };

    return (
        <Card className={`${styles.introStep} wps-wrap-introStep`}>
            <CardBody>
                <Heading className={`${styles.heading} wps-wrap-introStep__heading`}>
                    {__("We've updated WP Statistics to use a faster, more efficient database structure!", "wp-statistics")}
                </Heading>

                <p className={`${styles.text} wps-wrap-introStep__text`}>
                    {__("By running this migration, you'll safely move all your", "wp-statistics")} <span className={`${styles.bold} wps-wrap-introStep__bold`}>{__("older stats", "wp-statistics")}</span> {__("into the new system. Any visits recorded", "wp-statistics")} <span className={`${styles.bold} wps-wrap-introStep__bold`}>{__("after", "wp-statistics")}</span> {__("your update are already being stored in the new format, so you won't lose any current data", "wp-statistics")}
                </p>

                <div className={`${styles.infoBox} wps-wrap-introStep__infoBox`}>
                    <div>
                        <img src={InfoIcon} className={`${styles.infoIcon} wps-wrap-introStep__infoIcon`} alt="info-icon" />
                    </div>
                    <div className={`${styles.infoContent} wps-wrap-introStep__infoContent`}>
                        <p className={`${styles.infoText} wps-wrap-introStep__infoText`}>
                            {__("We recommend making a complete backup of your WordPress site. This is just in case you ever need to revert changes.", "wp-statistics")}{" "}
                            <a href="#" className={`${styles.infoLink} wps-wrap-introStep__infoLink`}>
                                {__("Learn how to back up your site", "wp-statistics")}
                            </a>
                        </p>
                        <p className={`${styles.infoText} wps-wrap-introStep__infoText`}>{__("Keep in mind the migration could take time. (anywhere from minutes to a few hours, depending on your site's size and server resources).", "wp-statistics")}</p>
                        <p className={`${styles.infoText} wps-wrap-introStep__infoText`}>{__("You can pause, cancel, or restart the migration at any point. Your old data remains untouched until the process fully completes.", "wp-statistics")}</p>
                    </div>
                </div>

                <p className={`${styles.description} wps-wrap-introStep__description`}>
                    {__("When you're ready, simply choose your preferred migration option below and click Next. You're in full control, and your data will remain safe every step of the way.", "wp-statistics")}
                </p>

                <Heading className={`${styles.subHeading} wps-wrap-introStep__subHeading`}>
                    {__("Choose Your Preferred Migration", "wp-statistics")}
                </Heading>

                <div className={`${styles.cardContainer} wps-wrap-introStep__cardContainer`}>
                    <MigrationCard
                        name={"full-detailed"}
                        option={option}
                        onClick={() => {
                            setOption("full-detailed");
                            const option = {
                                type: "full-detailed",
                                title: __("Full Detailed Migration", "wp-statistics"),
                                description: __("Moves all your historical data—visitors, devices, referral sources, search engines, and more—into the new database structure.", "wp-statistics"),
                                estimatedTime: __("Depending on your site's traffic history and server resources, this process can range from a few minutes to several hours.", "wp-statistics"),
                                whoFor: __("Users who want to preserve every bit of their analytics data without losing any detail.", "wp-statistics"),
                            };
                            setData(option);
                        }}
                    >
                        <div className={`${styles.cardHeader} wps-wrap-introStep__cardHeader`}>
                            <p className={`${styles.cardTitle} wps-wrap-introStep__cardTitle`}>{__("Full Detailed Migration", "wp-statistics")}</p>
                            <input 
                                type="radio" 
                                id="full-detailed" 
                                name="migration-type" 
                                value="full-detailed" 
                                checked={option === "full-detailed"} 
                                onChange={() => {
                                    setOption("full-detailed");
                                    const option = {
                                        type: "full-detailed",
                                        title: __("Full Detailed Migration", "wp-statistics"),
                                        description: __("Moves all your historical data—visitors, devices, referral sources, search engines, and more—into the new database structure.", "wp-statistics"),
                                        estimatedTime: __("Depending on your site's traffic history and server resources, this process can range from a few minutes to several hours.", "wp-statistics"),
                                        whoFor: __("Users who want to preserve every bit of their analytics data without losing any detail.", "wp-statistics"),
                                    };
                                    setData(option);
                                }}
                            />
                        </div>
                        <p className={`${styles.cardDescription} wps-wrap-introStep__cardDescription`}>
                            {__("Moves all your historical data—visitors, devices, referral sources, search engines, and more—into the new database structure.", "wp-statistics")}
                        </p>
                        <ul className={`${styles.cardList} wps-wrap-introStep__cardList`}>
                            <li>
                                <strong>{__("Estimated Time:", "wp-statistics")}</strong> {__("Depending on your site's traffic history and server resources, this process can range from a few minutes to several hours.", "wp-statistics")}
                            </li>
                            <li>
                                <strong>{__("Who It's For:", "wp-statistics")}</strong> {__("Users who want to preserve every bit of their analytics data without losing any detail.", "wp-statistics")}
                            </li>
                        </ul>
                    </MigrationCard>

                    <MigrationCard
                        option={option}
                        name={"summary-only"}
                        onClick={() => {
                            setOption("summary-only");
                            const option = {
                                type: "summary-only",
                                title: __("Summary-Only Migration", "wp-statistics"),
                                description: __("Quickly transfers only the visitor counts and page-view totals for older data. You'll lose detailed information (like devices, referrers, and search engines) for past visitors.", "wp-statistics"),
                                estimatedTime: __("Typically much faster than a full migration, often just a few minutes.", "wp-statistics"),
                                whoFor: __("Users who just need high-level trends and want the process done ASAP.", "wp-statistics"),
                            };
                            setData(option);
                        }}
                    >
                        <div className={`${styles.cardHeader} wps-wrap-introStep__cardHeader`}>
                            <p className={`${styles.cardTitle} wps-wrap-introStep__cardTitle`}>{__("Summary-Only Migration", "wp-statistics")}</p>
                            <input 
                                type="radio" 
                                id="summary-only" 
                                name="migration-type" 
                                value="summary-only" 
                                checked={option === "summary-only"} 
                                onChange={() => {
                                    setOption("summary-only");
                                    const option = {
                                        type: "summary-only",
                                        title: __("Summary-Only Migration", "wp-statistics"),
                                        description: __("Quickly transfers only the visitor counts and page-view totals for older data. You'll lose detailed information (like devices, referrers, and search engines) for past visitors.", "wp-statistics"),
                                        estimatedTime: __("Typically much faster than a full migration, often just a few minutes.", "wp-statistics"),
                                        whoFor: __("Users who just need high-level trends and want the process done ASAP.", "wp-statistics"),
                                    };
                                    setData(option);
                                }}
                            />
                        </div>
                        <p className={`${styles.cardDescription} wps-wrap-introStep__cardDescription`}>
                            {__("Quickly transfers only the visitor counts and page-view totals for older data. You'll lose detailed information (like devices, referrers, and search engines) for past visitors.", "wp-statistics")}
                        </p>
                        <ul className={`${styles.cardList} wps-wrap-introStep__cardList`}>
                            <li>
                                <strong>{__("Estimated Time:", "wp-statistics")}</strong> {__("Typically much faster than a full migration, often just a few minutes.", "wp-statistics")}
                            </li>
                            <li>
                                <strong>{__("Who It's For:", "wp-statistics")}</strong> {__("Users who just need high-level trends and want the process done ASAP.", "wp-statistics")}
                            </li>
                            <li>
                                <a>{__("Learn more about Summary-Only Migration", "wp-statistics")}</a>
                            </li>
                        </ul>
                    </MigrationCard>

                    <MigrationCard
                        option={option}
                        name={"hybrid"}
                        onClick={() => {
                            setOption("hybrid");
                            const option = {
                                type: "hybrid",
                                title: __("Hybrid Migration", "wp-statistics"),
                                description: __("Imports full, detailed stats for your most recent history—by default the last 90 days, while older data is brought in as summary-only.", "wp-statistics"),
                                estimatedTime: __("Longer than summary-only, but faster than a full detailed migration.", "wp-statistics"),
                                whoFor: __("Users who want to retain granular data for a recent timeframe while speeding up the migration for older records.", "wp-statistics"),
                            };
                            setData(option);
                        }}
                    >
                        <div className={`${styles.cardHeader} wps-wrap-introStep__cardHeader`}>
                            <p className={`${styles.cardTitle} wps-wrap-introStep__cardTitle`}>{__("Hybrid Migration", "wp-statistics")}</p>
                            <input 
                                type="radio" 
                                id="hybrid" 
                                name="migration-type" 
                                value="hybrid" 
                                checked={option === "hybrid"} 
                                onChange={() => {
                                    setOption("hybrid");
                                    const option = {
                                        type: "hybrid",
                                        title: __("Hybrid Migration", "wp-statistics"),
                                        description: __("Imports full, detailed stats for your most recent history—by default the last 90 days, while older data is brought in as summary-only.", "wp-statistics"),
                                        estimatedTime: __("Longer than summary-only, but faster than a full detailed migration.", "wp-statistics"),
                                        whoFor: __("Users who want to retain granular data for a recent timeframe while speeding up the migration for older records.", "wp-statistics"),
                                    };
                                    setData(option);
                                }}
                            />
                        </div>
                        <p className={`${styles.cardDescription} wps-wrap-introStep__cardDescription`}>
                            {__("Imports full, detailed stats for your most recent history—by default the last 90 days, while older data is brought in as summary-only.", "wp-statistics")}
                        </p>
                        <ul className={`${styles.cardList} wps-wrap-introStep__cardList`}>
                            <li>
                                <strong>{__("Estimated Time:", "wp-statistics")}</strong> {__("Longer than summary-only, but faster than a full detailed migration.", "wp-statistics")}
                            </li>
                            <li>
                                <strong>{__("Who It's For:", "wp-statistics")}</strong> {__("Users who want to retain granular data for a recent timeframe while speeding up the migration for older records.", "wp-statistics")}
                            </li>
                        </ul>
                        <div className={`${styles.hybridInputContainer} wps-wrap-introStep__hybridInputContainer`}>
                            <p>{__("Enter the number of days to migrate with full detail:", "wp-statistics")}</p>
                            <input
                                name="hybrid-value"
                                value={hybridDays}
                                className={`${styles.hybridInput} wps-wrap-introStep__hybridInput`}
                                onChange={(e) => setHybridDays(e.target.value)}
                            />
                        </div>
                    </MigrationCard>
                </div>
            </CardBody>

            <CardFooter className={`${styles.footer} wps-wrap-introStep__footer`}>
                <button
                    className={`${styles.nextButton} wps-wrap-introStep__nextButton`}
                    onClick={handleNext}
                >
                    {__("Next Step", "wp-statistics")}
                </button>
            </CardFooter>
        </Card>
    );
};

export default IntroStep;
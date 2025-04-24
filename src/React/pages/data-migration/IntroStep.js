import React, { useState, useEffect } from "@wordpress/element";
import { Card, CardBody, CardFooter, __experimentalHeading as Heading } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import InfoIcon from "../../../../assets/images/info-icon.svg";

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
        <Card>
            <CardBody>
                {/* {wps_js._("more_detail")} */}
                <Heading style={{ fontFamily: 500, fontSize: 24, lineHeight: 1.3, marginTop: "8px" }}>{__("We’ve updated WP Statistics to use a faster, more efficient database structure!", "wp-statistics")}</Heading>

                <p style={{ color: "#56585A", fontSize: 16 }}>
                    {__("By running this migration, you’ll safely move all your", "wp-statistics")} <span style={{ color: "#000" }}>{__("older stats", "wp-statistics")}</span> {__("into the new system. Any visits recorded", "wp-statistics")} <span style={{ color: "#000" }}>{__("after", "wp-statistics")}</span> {__("your update are already being stored in the new format, so you won’t lose any current data", "wp-statistics")}
                </p>

                <div
                    style={{
                        backgroundColor: "#F6FAFF",
                        border: "1px solid #4FA1FF66",
                        borderRadius: "8px",
                        padding: "16px",
                        margin: "16px 0px",
                        display: "flex",
                        alignItems: "start",
                        gap: 8,
                    }}
                >
                    <div>
                        <img src={InfoIcon} style={{ width: "20px", height: "20px", marginTop: 5 }} alt="info-icon" />
                    </div>
                    <div style={{ display: "flex", flexDirection: "column", gap: "10px" }}>
                        <p style={{ fontSize: "14px", margin: "0px" }}>
                            {__("We recommend making a complete backup of your WordPress site. This is just in case you ever need to revert changes.", "wp-statistics")}{" "}
                            <a href="#" style={{ textDecoration: "underline" }}>
                                {__("Learn how to back up your site", "wp-statistics")}
                            </a>
                        </p>
                        <p style={{ fontSize: "14px", margin: "0px" }}>{__("Keep in mind the migration could take time. (anywhere from minutes to a few hours, depending on your site’s size and server resources).", "wp-statistics")}</p>
                        <p style={{ fontSize: "14px", margin: "0px" }}>{__("You can pause, cancel, or restart the migration at any point. Your old data remains untouched until the process fully completes.", "wp-statistics")}</p>
                    </div>
                </div>

                <p style={{ fontSize: "16px" }}>{__("When you’re ready, simply choose your preferred migration option below and click Next. You’re in full control, and your data will remain safe every step of the way.", "wp-statistics")}</p>

                <Heading style={{ fontFamily: 500, fontSize: 24, lineHeight: 1.3, marginTop: 32, marginBottom: 16 }}>{__("Choose Your Preferred Migration", "wp-statistics")}</Heading>

                <div style={{ display: "flex", flexDirection: "column", gap: "12px" }}>
                    {/* Option 1 */}
                    <Card
                        style={{
                            border: option === "full-detailed" ? "1px solid #1e87f0" : "1px solid #ccc",
                            borderRadius: 8,
                            padding: "24px",
                            cursor: "pointer",
                            boxShadow: "none",
                        }}
                        onClick={() => {
                            setOption("full-detailed");
                            const option = {
                                type: "full-detailed",
                                title: __("Full Detailed Migration", "wp-statistics"),
                                description: __("Moves all your historical data—visitors, devices, referral sources, search engines, and more—into the new database structure.", "wp-statistics"),
                                estimatedTime: __("Depending on your site’s traffic history and server resources, this process can range from a few minutes to several hours.", "wp-statistics"),
                                whoFor: __("Users who want to preserve every bit of their analytics data without losing any detail.", "wp-statistics"),
                            };
                            setData(option);
                        }}
                    >
                        <CardBody style={{ padding: "0px" }}>
                            <div
                                style={{
                                    display: "flex",
                                    justifyContent: "space-between",
                                    alignItems: "center",
                                    width: "100%",
                                }}
                            >
                                <p style={{ fontSize: "14px", fontFamily: 500, color: "#1E1E20", fontWeight: "700", margin: "0px" }}>{__("Full Detailed Migration", "wp-statistics")}</p>
                                <input type="radio" id="full-detailed" name="full-detailed" value="full-detailed" checked={option === "full-detailed"} />
                            </div>
                            <p style={{ color: "#56585A" }}>{__("Moves all your historical data—visitors, devices, referral sources, search engines, and more—into the new database structure.", "wp-statistics")}</p>
                            <ul style={{ listStyle: "disc", paddingLeft: "30px" }}>
                                <li>
                                    <strong>{__("Estimated Time:", "wp-statistics")}</strong> {__("Depending on your site’s traffic history and server resources, this process can range from a few minutes to several hours.", "wp-statistics")}
                                </li>
                                <li>
                                    <strong>{__("Who It’s For:", "wp-statistics")}</strong> {__("Users who want to preserve every bit of their analytics data without losing any detail.", "wp-statistics")}
                                </li>
                            </ul>
                        </CardBody>
                    </Card>

                    {/* Option 2 */}
                    <Card
                        style={{
                            border: option === "summary-only" ? "1px solid #1e87f0" : "1px solid #ccc",
                            borderRadius: 8,
                            padding: "24px",
                            cursor: "pointer",
                            boxShadow: "none",
                        }}
                        onClick={() => {
                            setOption("summary-only");
                            const option = {
                                type: "summary-only",
                                title: __("Summary-Only Migration", "wp-statistics"),
                                description: __("Quickly transfers only the visitor counts and page-view totals for older data. You’ll lose detailed information (like devices, referrers, and search engines) for past visitors.", "wp-statistics"),
                                estimatedTime: __("Typically much faster than a full migration, often just a few minutes.", "wp-statistics"),
                                whoFor: __("Users who just need high-level trends and want the process done ASAP.", "wp-statistics"),
                            };
                            setData(option);
                        }}
                    >
                        <CardBody style={{ padding: "0px" }}>
                            <div
                                style={{
                                    display: "flex",
                                    justifyContent: "space-between",
                                    alignItems: "center",
                                    width: "100%",
                                }}
                            >
                                <p style={{ margin: "0px", fontSize: "14px", fontFamily: 500, color: "#1E1E20", fontWeight: "700" }}>{__("Summary-Only Migration", "wp-statistics")}</p>
                                <input type="radio" id="summary-only" name="summary-only" value="summary-only" checked={option === "summary-only"} />
                            </div>
                            <p style={{ color: "#56585A" }}>{__("Quickly transfers only the visitor counts and page-view totals for older data. You’ll lose detailed information (like devices, referrers, and search engines) for past visitors.", "wp-statistics")}</p>
                            <ul style={{ listStyle: "disc", paddingLeft: "30px" }}>
                                <li>
                                    <strong>{__("Estimated Time:", "wp-statistics")}</strong> {__("Typically much faster than a full migration, often just a few minutes.", "wp-statistics")}
                                </li>
                                <li>
                                    <strong>{__("Who It’s For:", "wp-statistics")}</strong> {__("Users who just need high-level trends and want the process done ASAP.", "wp-statistics")}
                                </li>
                                <li>
                                    <a>{__("Learn more about Summary-Only Migration", "wp-statistics")}</a>
                                </li>
                            </ul>
                        </CardBody>
                    </Card>

                    {/* Option 3 */}
                    <Card
                        style={{
                            border: option === "hybrid" ? "1px solid #1e87f0" : "1px solid #ccc",
                            borderRadius: 8,
                            padding: "24px",
                            cursor: "pointer",
                            boxShadow: "none",
                        }}
                        onClick={() => {
                            setOption("hybrid");
                            const option = {
                                type: "hybrid",
                                title: __("Hybrid Migration", "wp-statistics"),
                                description: __("Imports full, detailed stats for your most recent history—by default the last 90 days, while older data is brought in as summary-only.", "wp-statistics"),
                                estimatedTime: __("Longer than summary-only, but faster than a full detailed migration.", "wp-statistics"),
                                whoFor: __("Users who want to retain granular data for a recent timeframe while speeding up the migration for older records.", "wp-statistics"),
                            };
                            setData(option);
                        }}
                    >
                        <CardBody style={{ padding: "0px" }}>
                            <div
                                style={{
                                    display: "flex",
                                    justifyContent: "space-between",
                                    alignItems: "center",
                                    width: "100%",
                                }}
                            >
                                <p style={{ margin: "0px", fontSize: "14px", fontFamily: 500, color: "#1E1E20", fontWeight: "700" }}>{__("Hybrid Migration", "wp-statistics")}</p>
                                <input type="radio" id="hybrid" name="hybrid" value="hybrid" checked={option === "hybrid"} />
                            </div>
                            <p style={{ color: "#56585A" }}>{__("Imports full, detailed stats for your most recent history—by default the last 90 days, while older data is brought in as summary-only.", "wp-statistics")}</p>
                            <ul style={{ listStyle: "disc", paddingLeft: "30px" }}>
                                <li>
                                    <strong>{__("Estimated Time:", "wp-statistics")}</strong> {__("Longer than summary-only, but faster than a full detailed migration.", "wp-statistics")}
                                </li>
                                <li>
                                    <strong>{__("Who It’s For:", "wp-statistics")}</strong> {__("Users who want to retain granular data for a recent timeframe while speeding up the migration for older records.", "wp-statistics")}
                                </li>
                            </ul>
                            <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
                                <p>{__("Enter the number of days to migrate with full detail:", "wp-statistics")}</p>
                                <input
                                    name="hybrid-value"
                                    value={hybridDays}
                                    style={{
                                        outline: "none",
                                        border: "1px solid #DADCE0",
                                        width: "46px",
                                        height: "32px",
                                        borderRadius: "3px",
                                        padding: "0px 7px",
                                        textAlign: "center",
                                    }}
                                    onChange={(e) => setHybridDays(e.target.value)}
                                />
                            </div>
                        </CardBody>
                    </Card>
                </div>
            </CardBody>

            <CardFooter style={{ display: "flex", justifyContent: "flex-end", paddingTop: "32px", paddingBottom: "32px" }}>
                <button
                    style={{
                        background: "#404BF2",
                        outline: "none",
                        border: "none",
                        padding: "12px 16px",
                        borderRadius: "4px",
                        color: "white",
                        cursor: "pointer",
                    }}
                    onClick={handleNext}
                >
                    {__("Next Step", "wp-statistics")}
                </button>
            </CardFooter>
        </Card>
    );
};

export default IntroStep;

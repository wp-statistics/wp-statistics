import React, { useState, useEffect } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { Card, CardBody, CardFooter, __experimentalHeading as Heading } from "@wordpress/components";
import SelectedRadio from "../../../../images/selected-radio.png";
import ConfirmationInfo from "../../components/Confirmation-Info";

const ConfirmationStep = ({ handleStep }) => {
    const [data, setData] = useState({});
    const storage = localStorage.getItem("wps_migration_option");

    useEffect(() => {
        if (storage) {
            setData(JSON.parse(storage));
        }
    }, [storage]);

    return (
        <Card>
            <CardBody>
                <Heading
                    style={{
                        fontFamily: 500,
                        fontSize: "24px",
                        lineHeight: 1.3,
                        marginTop: "8px",
                        marginBottom: "16px",
                    }}
                >
                    {__("Confirmation Step", "wp-statistics")}
                </Heading>
                <Card
                    style={{
                        border: "1px solid #EEEFF1",
                        borderRadius: "8px",
                        padding: "24px",
                        cursor: "pointer",
                        boxShadow: "none",
                        background: "#FAFAFB",
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
                            <p
                                style={{
                                    fontSize: "14px",
                                    fontFamily: 500,
                                    color: "#1E1E20",
                                    fontWeight: "700",
                                    margin: "0px",
                                }}
                            >
                                {data?.title}
                            </p>
                            <img
                                src={SelectedRadio}
                                alt="radio-select"
                                style={{
                                    width: "17px",
                                    height: "17px",
                                }}
                            />
                            {/* <input className="radio-checked" type="radio" disabled id={`1`} name="migration-option" value={"1"} checked={true} /> */}
                        </div>
                        <p style={{ color: "#56585A" }}>{data?.description}</p>
                        <ul style={{ listStyle: "disc", paddingLeft: "30px" }}>
                            <li>
                                <strong>{__("Estimated Time:", "wp-statistics")}</strong> {data?.estimatedTime}
                            </li>
                            <li>
                                <strong>{__("Who It’s For:", "wp-statistics")}</strong> {data?.whoFor}
                            </li>
                        </ul>
                    </CardBody>
                </Card>
                <div
                    style={{
                        display: "flex",
                        gap: "10px",
                        flexDirection: "column",
                        marginTop: "28px",
                    }}
                >
                    <ConfirmationInfo label={"What’s Next?"} detail={"We’ll migrate all of your historical data—visitors, devices, search engines, referrers, and more—into the new database structure."} />
                    <ConfirmationInfo label={"What’s Migrated?"} detail={"Absolutely everything from your past analytics, so you retain complete visibility into your site’s historical data."} />
                    <ConfirmationInfo label={"What’s Lost?"} detail={"Nothing! All detailed stats will be preserved."} />
                    <ConfirmationInfo label={"Estimated Time:"} detail={"Depending on the size of your site and server performance, it can take anywhere from minutes to a few hours."} />

                    <div style={{ padding: "15px 0px" }}>
                        <p style={{ fontSize: "15px" }}>
                            <strong>{__("Regardless of the choice,", "wp-statistics")}</strong> {__("you could also include these reminders at the bottom of the confirmation step:", "wp-statistics")}
                        </p>
                        <ul style={{ listStyle: "disc", paddingLeft: "22px", margin: "5px 0px" }}>
                            <li style={{ fontSize: "14px" }}>{__("You can pause, cancel, or restart the migration at any time.", "wp-statistics")}</li>
                            <li style={{ fontSize: "14px" }}>{__("Nothing is deleted from your old data source until the migration is fully complete.", "wp-statistics")}</li>
                            <li style={{ fontSize: "14px" }}>
                                {__("Need more details or help?", "wp-statistics")} <a href="">{__("Check our Migration FAQs or contact support.", "wp-statistics")}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </CardBody>
            <CardFooter style={{ flexDirection: "column" }}>
                <div>
                    <p style={{ fontWeight: "500", fontSize: "16px", marginBottom: "0px" }}>{__("Ready to proceed?", "wp-statistics")}</p>
                    <p style={{ fontWeight: "400", fontSize: "14px", color: "#56585A", margin: "0px" }}>
                        {__("You can", "wp-statistics")} <span style={{ fontWeight: "500", color: "#000" }}>{__("go back", "wp-statistics")}</span> {__("to change the number of days or pick a different migration method. Or, click", "wp-statistics")} <span style={{ fontWeight: "500", color: "#000" }}>{__("Start Migration", "wp-statistics")}</span> {__("to begin.", "wp-statistics")}
                    </p>
                </div>
                <div
                    style={{
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "space-between",
                        width: "100%",
                        padding: "10px 0px",
                    }}
                >
                    <p style={{ cursor: "pointer" }} onClick={() => handleStep("step1")}>
                        {__("< Go Back", "wp-statistics")}
                    </p>
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
                        onClick={() => handleStep("step3")}
                    >
                        {__("Start Migration", "wp-statistics")}
                    </button>
                </div>
            </CardFooter>
        </Card>
    );
};

export default ConfirmationStep;

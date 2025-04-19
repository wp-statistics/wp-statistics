import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { Card, CardBody, CardFooter, Button } from "@wordpress/components";

const Step2 = ({ handleStep }) => {
    const [option, setOption] = useState("a");
    return (
        <Card>
            <CardBody>
                <h2
                    style={{
                        fontFamily: 500,
                        fontSize: 24,
                        lineHeight: 1.3,
                        marginTop: "8px",
                        marginBottom: "16px",
                    }}
                >
                    {__("Confirmation Step", "wp-gutenberg")}
                </h2>
                <Card
                    style={{
                        border: "1px solid #EEEFF1",
                        borderRadius: 8,
                        padding: "24px",
                        cursor: "pointer",
                        boxShadow: "none",
                        background: "#FAFAFB",
                    }}
                    onClick={() => {}}
                >
                    <CardBody
                        style={{
                            padding: "0px",
                        }}
                    >
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
                                }}
                            >
                                Full Detailed Migration
                            </p>
                            <input type="radio" disabled id={`1`} name="migration-option" value={"1"} checked={true} />
                        </div>
                        <p
                            style={{
                                padding: "8px 0px",
                                color: "#56585A",
                            }}
                        >
                            Moves all your historical data—visitors, devices, referral sources, search engines, and more—into the new database structure.
                        </p>
                        <ul
                            style={{
                                listStyle: "disc",
                                paddingLeft: "30px",
                            }}
                        >
                            <li>
                                <span
                                    style={{
                                        fontWeight: "bold",
                                    }}
                                >
                                    Estimated Time:
                                </span>{" "}
                                Depending on your site’s traffic history and server resources, this process can range from a few minutes to several hours.
                            </li>
                            <li>
                                <span
                                    style={{
                                        fontWeight: "bold",
                                    }}
                                >
                                    Who It’s For:
                                </span>{" "}
                                Users who want to preserve every bit of their analytics data without losing any detail.
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
                    <p
                        style={{
                            fontSize: "15px",
                        }}
                    >
                        <span
                            style={{
                                fontWeight: "bold",
                            }}
                        >
                            What’s Next?
                        </span>{" "}
                        We’ll migrate all of your historical data—visitors, devices, search engines, referrers, and more—into the new database structure.
                    </p>
                    <p
                        style={{
                            fontSize: "15px",
                        }}
                    >
                        <span
                            style={{
                                fontWeight: "bold",
                            }}
                        >
                            What’s Migrated?
                        </span>{" "}
                        Absolutely everything from your past analytics, so you retain complete visibility into your site’s historical data.{" "}
                    </p>
                    <p
                        style={{
                            fontSize: "15px",
                        }}
                    >
                        <span
                            style={{
                                fontWeight: "bold",
                            }}
                        >
                            What’s Lost?
                        </span>{" "}
                        Nothing! All detailed stats will be preserved.{" "}
                    </p>
                    <p
                        style={{
                            fontSize: "15px",
                        }}
                    >
                        <span
                            style={{
                                fontWeight: "bold",
                            }}
                        >
                            Estimated Time:
                        </span>{" "}
                        Depending on the size of your site and server performance, it can take anywhere from minutes to a few hours.
                    </p>
                    <div style={{ padding: "15px 0px" }}>
                        <p
                            style={{
                                fontSize: "15px",
                            }}
                        >
                            <span
                                style={{
                                    fontWeight: "bold",
                                }}
                            >
                                Regardless of the choice,
                            </span>{" "}
                            you could also include these reminders at the bottom of the confirmation step:
                        </p>
                        <ul
                            style={{
                                listStyle: "disc",
                                paddingLeft: "22px",
                                margin: "5px 0px",
                            }}
                        >
                            <li style={{ fontSize: "14px" }}>You can pause, cancel, or restart the migration at any time.</li>
                            <li style={{ fontSize: "14px" }}>Nothing is deleted from your old data source until the migration is fully complete.</li>
                            <li>
                                <p style={{ fontSize: "14px" }}>
                                    Need more details or help? <a href="">Check our Migration FAQs or contact support.</a>
                                </p>
                            </li>
                        </ul>
                    </div>
                </div>
            </CardBody>
            <CardFooter
                style={{
                    flexDirection: "column",
                }}
            >
                <div>
                    <p
                        style={{
                            fontWeight: "500",
                            fontSize: "16px",
                        }}
                    >
                        Ready to proceed?
                    </p>
                    <p
                        style={{
                            fontWeight: "400",
                            fontSize: "14px",
                            color: "#56585A",
                            paddingTop: "4px",
                        }}
                    >
                        You can{" "}
                        <span
                            style={{
                                fontWeight: "500",
                                color: "#000",
                            }}
                        >
                            go back
                        </span>{" "}
                        to change the number of days or pick a different migration method.Or, click{" "}
                        <span
                            style={{
                                fontWeight: "500",
                                color: "#000",
                            }}
                        >
                            Start Migration
                        </span>{" "}
                        to begin.
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
                    <p
                        style={{
                            cursor: "pointer",
                        }}
                        onClick={() => handleStep("step1")}
                    >{`< Go Back`}</p>
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
                        onClick={() => {}}
                    >
                        Start Migration
                    </button>
                </div>
            </CardFooter>
        </Card>
    );
};

export default Step2;

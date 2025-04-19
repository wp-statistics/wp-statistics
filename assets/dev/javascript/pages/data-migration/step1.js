import { useState } from "@wordpress/element";
import { Card, CardBody, CardFooter, Button } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import InfoIcon from "../../../../images/info-icon.svg";

const Step1 = ({ handleStep }) => {
    const [option, setOption] = useState("a");
    return (
        <Card>
            <CardBody>
                <h2
                    style={{
                        fontFamily: 500,
                        fontSize: 24,
                        lineHeight: 1.3,
                        margin: "8px 0px",
                    }}
                >
                    {__("We’ve updated WP Statistics to use a faster, more efficient database structure!", "wp-gutenberg")}
                </h2>
                <p
                    style={{
                        color: "#56585A",
                        fontSize: 16,
                    }}
                >
                    By running this migration, you’ll safely move all your{" "}
                    <span
                        style={{
                            color: "#000",
                        }}
                    >
                        older stats
                    </span>{" "}
                    into the new system. Any visits recorded{" "}
                    <span
                        style={{
                            color: "#000",
                        }}
                    >
                        after
                    </span>{" "}
                    your update are already being stored in the new format, so you won’t lose any current data
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
                        <img
                            src={InfoIcon}
                            style={{
                                width: "20px",
                                height: "20px",
                                marginTop: 5,
                            }}
                            alt="info-icon"
                        />
                    </div>
                    <div
                        style={{
                            display: "flex",
                            flexDirection: "column",
                            gap: "10px",
                        }}
                    >
                        <p
                            style={{
                                fontSize: "14px",
                            }}
                        >
                            We recommend making a complete backup of your WordPress site. This is just in case you ever need to revert changes.{" "}
                            <a
                                href="#"
                                style={{
                                    textDecoration: "underline",
                                }}
                            >
                                Learn how to back up your site
                            </a>
                        </p>
                        <p
                            style={{
                                fontSize: "14px",
                            }}
                        >
                            Keep in mind the migration could take time. (anywhere from minutes to a few hours, depending on your site’s size and server resources).
                        </p>
                        <p
                            style={{
                                fontSize: "14px",
                            }}
                        >
                            You can pause, cancel, or restart the migration at any point. Your old data remains untouched until the process fully completes.
                        </p>
                    </div>
                </div>
                <p
                    style={{
                        fontSize: "16px",
                    }}
                >
                    When you’re ready, simply choose your preferred migration option below and click Next. You’re in full control, and your data will remain safe every step of the way.
                </p>
                <h2
                    style={{
                        fontFamily: 500,
                        fontSize: 24,
                        lineHeight: 1.3,
                        marginTop: 32,
                        marginBottom: 16,
                    }}
                >
                    {__("Choose Your Preferred Migration", "wp-gutenberg")}
                </h2>

                <div style={{ display: "flex", flexDirection: "column", gap: "12px" }}>
                    <Card
                        style={{
                            border: option === "1" ? "2px solid #1e87f0" : "1px solid #ccc",
                            borderRadius: 8,
                            padding: "24px",
                            cursor: "pointer",
                            boxShadow: "none",
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
                                <input type="radio" id={`1`} name="migration-option" value={"1"} checked={option === "1"} onChange={() => setOption("1")} />
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
                    <Card
                        style={{
                            border: option === "2" ? "2px solid #1e87f0" : "1px solid #ccc",
                            borderRadius: 8,
                            padding: "24px",
                            cursor: "pointer",
                            boxShadow: "none",
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
                                    Summary-Only Migration
                                </p>
                                <input type="radio" id={`2`} name="migration-option" value={"2"} checked={option === "2"} onChange={() => setOption("2")} />
                            </div>
                            <p
                                style={{
                                    padding: "8px 0px",
                                    color: "#56585A",
                                }}
                            >
                                Quickly transfers only the visitor counts and page-view totals for older data. You’ll lose detailed information (like devices, referrers, and search engines) for past visitors.
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
                                    Typically much faster than a full migration, often just a few minutes.
                                </li>
                                <li>
                                    <span
                                        style={{
                                            fontWeight: "bold",
                                        }}
                                    >
                                        Who It’s For:
                                    </span>{" "}
                                    Users who just need high-level trends and want the process done ASAP.
                                </li>
                                <li>
                                    <a>Learn more about Summary-Only Migration</a>
                                </li>
                            </ul>
                        </CardBody>
                    </Card>
                    <Card
                        style={{
                            border: option === "3" ? "2px solid #1e87f0" : "1px solid #ccc",
                            borderRadius: 8,
                            padding: "24px",
                            cursor: "pointer",
                            boxShadow: "none",
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
                                    Hybrid Migration
                                </p>
                                <input type="radio" id={`2`} name="migration-option" value={"3"} checked={option === "3"} onChange={() => setOption("3")} />
                            </div>
                            <p
                                style={{
                                    padding: "8px 0px",
                                    color: "#56585A",
                                }}
                            >
                                Imports full, detailed stats for your most recent history—by default the last 90 days, while older data is brought in as summary-only.
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
                                    Longer than summary-only, but faster than a full detailed migration.
                                </li>
                                <li>
                                    <span
                                        style={{
                                            fontWeight: "bold",
                                        }}
                                    >
                                        Who It’s For:
                                    </span>{" "}
                                    Users who want to retain granular data for a recent timeframe while speeding up the migration for older records.
                                </li>
                            </ul>
                            <div
                                style={{
                                    display: "flex",
                                    alignItems: "center",
                                    gap: "10px",
                                }}
                            >
                                <p>Enter the number of days to migrate with full detail:</p>
                                <input
                                    style={{
                                        outline: "none",
                                        border: "1px solid #DADCE0",
                                        width: "46px",
                                        height: "32px",
                                        borderRadius: "3px",
                                    }}
                                />
                            </div>
                        </CardBody>
                    </Card>
                </div>
            </CardBody>
            <CardFooter
                style={{
                    display: "flex",
                    justifyContent: "flex-end",
                    paddingTop: "32px",
                    paddingBottom: "32px",
                }}
            >
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
                    onClick={() => handleStep("step2")}
                >
                    Next Step
                </button>
            </CardFooter>
        </Card>
    );
};

export default Step1;

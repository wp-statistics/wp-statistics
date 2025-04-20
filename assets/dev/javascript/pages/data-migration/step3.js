import { Card, CardBody, CardFooter, Button } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import InfoIcon from "../../../../images/information.svg";

const Step3 = ({ handleStep }) => {
    const isCompleted = true;
    const tasks = [
        { id: 1, name: "Visitor Records", status: "Completed", records: "5,200 records migrated" },
        { id: 2, name: "Page Views Data", status: "Completed", records: "12,300 records migrated" },
        { id: 3, name: "Geo-Location Data", status: "In Progress", progressText: "(next)", records: "4,100 of 10,000 completed", isBold: true },
        { id: 4, name: "Referral Traffic", status: "Pending", records: "0 of 8,400 completed" },
        { id: 5, name: "Author Performance Data", status: "Pending", records: "0 of 2,000 completed" },
    ];

    // --- Style Objects ---
    // It's often cleaner to define style objects outside the component or memoize them
    const tableStyle = {
        width: "100%",
        borderCollapse: "collapse",
        fontFamily: "Arial, sans-serif",
        overflow: "hidden",
        borderRadius: "8px",
    };

    const thStyle = {
        backgroundColor: "#f8f9fa",
        color: "#6c757d",
        textAlign: "left",
        padding: "12px 15px",
        fontWeight: 600, // Use numbers or strings for fontWeight
        borderBottom: "1px solid #e0e0e0",
    };

    const tdBaseStyle = {
        // Base style for table data cells
        padding: "12px 15px",
        borderBottom: "1px solid #eef2f7",
        verticalAlign: "middle",
        background: "white",
    };

    const iconStyle = {
        marginRight: "5px",
    };

    // Helper function to get status details (icon, color, text)
    const getStatusDetails = (status, progressText = "") => {
        switch (status) {
            case "Completed":
                return { icon: "✅", text: "Completed" };
            case "In Progress":
                return { icon: "🔄", text: `In Progress ${progressText}`.trim() };
            case "Pending":
                return { icon: "⏳", text: "Pending" };
            default:
                return { icon: "", text: status }; // Fallback
        }
    };
    return (
        <Card
            style={{
                width: window.innerWidth <= 768 ? "100%" : 774,
            }}
        >
            <CardBody>
                <h2
                    style={{
                        fontFamily: 500,
                        fontSize: 18,
                        lineHeight: 1.3,
                        marginTop: "8px",
                        marginBottom: "16px",
                    }}
                >
                    {__("Overall Progress", "wp-gutenberg")}
                </h2>
                <div>
                    <h4
                        style={{
                            fontFamily: 500,
                            fontSize: 16,
                            marginBottom: 10,
                        }}
                    >
                        {isCompleted ? "100%" : "60%"} Completed
                    </h4>
                    <div
                        style={{
                            height: "5px",
                            borderRadius: "24px",
                            background: "#E7E7E8",
                            width: "100%",
                        }}
                    >
                        <div
                            style={{
                                height: "5px",
                                borderRadius: "24px",
                                background: "#019939",
                                width: isCompleted ? "100%" : "60%",
                            }}
                        ></div>
                    </div>
                </div>
                <div
                    style={{
                        border: "2px solid #eef2f7",
                        borderRadius: "8px",
                        margin: "24px 0px",
                    }}
                >
                    <table style={tableStyle}>
                        <thead>
                            <tr>
                                <th style={thStyle}>Task Name</th>
                                <th style={thStyle}>Status</th>
                                <th style={thStyle}>Records</th>
                            </tr>
                        </thead>
                        <tbody>
                            {tasks.map((task, index) => {
                                const statusDetails = getStatusDetails(task.status, task.progressText);
                                const isLastRow = index === tasks.length - 1;

                                // Define cell styles, removing bottom border for the last row
                                const tdDynamicStyle = {
                                    ...tdBaseStyle, // Include base styles
                                    borderBottom: isLastRow ? "none" : tdBaseStyle.borderBottom, // Conditional border
                                };

                                // Define specific styles based on task properties (e.g., boldness)
                                const taskNameStyle = {
                                    ...tdDynamicStyle,
                                    color: "#333",
                                    fontWeight: task.isBold ? 600 : "normal",
                                };

                                const statusStyle = {
                                    ...tdDynamicStyle,
                                    color: statusDetails.color,
                                    fontWeight: task.isBold ? 600 : "normal",
                                };

                                const recordsStyle = {
                                    ...tdDynamicStyle,
                                    color: task.isBold ? "#333" : "#6c757d", // Darker grey default, black if bold
                                    fontWeight: task.isBold ? 600 : "normal",
                                };

                                return (
                                    // Use a unique key for each row, essential for React lists
                                    <tr key={task.id}>
                                        <td style={taskNameStyle}>{task.name}</td>
                                        <td style={statusStyle}>
                                            <span style={iconStyle}>{statusDetails.icon}</span>
                                            {statusDetails.text}
                                        </td>
                                        <td style={recordsStyle}>{task.records}</td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
                {!isCompleted && (
                    <div style={{ color: "#333" }}>
                        <h2 style={{ fontSize: "16px", marginBottom: "12px", marginBottom: "8px" }}>Status Icons Explanation:</h2>
                        <ul
                            style={{
                                listStyle: "disc",
                                paddingLeft: "5px",
                            }}
                        >
                            <li style={{ fontSize: "15px", marginBottom: "14px", display: "flex", alignItems: "center" }}>
                                <span role="img" aria-label="Completed" style={{ marginRight: "10px", fontSize: "1.2em", color: "green" }}>
                                    ✅
                                </span>
                                <span>Completed: The task is fully finished.</span>
                            </li>
                            <li style={{ fontSize: "15px", marginBottom: "14px", display: "flex", alignItems: "center" }}>
                                <span role="img" aria-label="In Progress" style={{ marginRight: "10px", fontSize: "1.2em" }}>
                                    🔄
                                </span>
                                <span>In Progress: Task is currently migrating.</span>
                            </li>
                            <li style={{ fontSize: "15px", display: "flex", alignItems: "center" }}>
                                <span role="img" aria-label="Pending" style={{ marginRight: "10px", fontSize: "1.2em" }}>
                                    ⏳
                                </span>
                                <span>Pending: Task is queued and will start after the current one completes.</span>
                            </li>
                        </ul>
                    </div>
                )}
                {isCompleted && (
                    <div
                        style={{
                            maxWidth: "600px",
                            marginTop: "40px",
                            marginBottom: "20px",
                            fontFamily: "sans-serif",
                            color: "#333",
                        }}
                    >
                        <h2
                            style={{
                                fontSize: "1.2em",
                                fontWeight: "bold",
                                marginBottom: "10px",
                            }}
                        >
                            <span style={{ marginRight: "5px" }}>🎉</span> Migration Completed Successfully!
                        </h2>
                        <p
                            style={{
                                fontSize: 15,
                                color: "#0C0C0D",
                            }}
                        >
                            All your historical data has been successfully migrated!
                        </p>
                        <p
                            style={{
                                fontSize: 15,
                                color: "#56585A",
                            }}
                        >
                            {" "}
                            You can now take full advantage of WP Statistics' new structure.
                        </p>

                        <p
                            style={{
                                fontWeight: "bold",
                                marginBottom: "2px",
                                marginTop: "8px",
                                fontSize: 15,
                            }}
                        >
                            Next Steps:
                        </p>

                        <ul
                            style={{
                                listStyleType: "disc",
                                marginLeft: "20px",
                                paddingLeft: "20px",
                                color: "#555",
                                lineHeight: 1.6,
                                margin: 0,
                            }}
                        >
                            <li style={{ marginBottom: "5px", color: "#56585A", fontSize: 15 }}>
                                Check out your updated stats in the{" "}
                                <a href="#" style={{ color: "#0073aa", textDecoration: "underline" }}>
                                    WP Statistics Dashboard
                                </a>
                                .
                            </li>
                            <li style={{ marginBottom: "5px", color: "#56585A", fontSize: 15 }}>
                                If you have any questions, visit our{" "}
                                <a href="#" style={{ color: "#0073aa", textDecoration: "underline" }}>
                                    Migration FAQs
                                </a>{" "}
                                or{" "}
                                <a href="#" style={{ color: "#0073aa", textDecoration: "underline" }}>
                                    contact support
                                </a>
                                .
                            </li>
                        </ul>
                    </div>
                )}
            </CardBody>
            {!isCompleted && (
                <CardFooter>
                    <div>
                        <div
                            style={{
                                backgroundColor: "#FEF9F5" /* Light yellow/beige background */,
                                border: "1px solid #E68C3F80" /* Light orange border */,
                                borderRadius: "8px",
                                padding: "13px 16px",
                                color: "#333",
                                marginBottom: "24px",
                                marginTop: "12px",
                            }}
                        >
                            <div
                                style={{
                                    display: "flex",
                                    alignItems: "start",
                                    gap: "12px",
                                }}
                            >
                                <img src={InfoIcon} alt="info" />
                                <div>
                                    <div
                                        style={{
                                            display: "flex",
                                            alignItems: "center",
                                            marginBottom: "10px",
                                        }}
                                    >
                                        <h3
                                            style={{
                                                margin: "0",
                                                fontSize: "14px",
                                                fontWeight: "bold",
                                                color: "#E68C3F" /* Light orange for the title */,
                                            }}
                                        >
                                            Important Notes
                                        </h3>
                                    </div>
                                    <ul
                                        style={{
                                            listStyleType: "disc",
                                            paddingLeft: "15px",
                                            margin: "0",
                                        }}
                                    >
                                        <li style={{ marginBottom: "8px", fontSize: 14 }}>
                                            <strong>Migration time</strong> varies based on your data size and server resources.
                                        </li>
                                        <li style={{ marginBottom: "8px", fontSize: 14 }}>
                                            You can continue using your site in <strong style={{ fontWeight: "bold" }}>another browser tab</strong>, but <strong style={{ fontWeight: "bold" }}>this migration page must remain open</strong>.
                                        </li>
                                        <li style={{ marginBottom: "8px", fontSize: 14 }}>
                                            If the migration is <strong style={{ fontWeight: "bold" }}>paused</strong> or <strong style={{ fontWeight: "bold" }}>interrupted</strong>, returning to this page resumes it from where you left off.
                                        </li>
                                        <li style={{ fontSize: 14 }}>
                                            <strong style={{ fontWeight: "bold" }}>No data is deleted</strong> until the migration is fully complete—feel free to pause or cancel if you need to, without losing your old records.
                                        </li>
                                    </ul>
                                </div>
                            </div>
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
                            <button
                                style={{
                                    background: "#fff",
                                    outline: "none",
                                    border: "1px solid #EEEFF1",
                                    padding: "12px 16px",
                                    borderRadius: "4px",
                                    cursor: "pointer",
                                    color: "#56585A",
                                }}
                                onClick={() => handleStep("step2")}
                            >
                                Cancel
                            </button>
                            <button
                                style={{
                                    background: "#EEEFF1",
                                    outline: "none",
                                    border: "none",
                                    padding: "12px 16px",
                                    borderRadius: "4px",
                                    color: "#56585A",
                                    cursor: "pointer",
                                }}
                            >
                                Pause
                            </button>
                        </div>
                    </div>
                </CardFooter>
            )}
        </Card>
    );
};

export default Step3;

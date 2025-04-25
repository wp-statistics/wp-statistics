import { Card, CardBody, CardFooter, __experimentalHeading as Heading } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import InfoIcon from "../../../../images/information.svg";

const ProgressStep = ({ handleStep }) => {
    const isCompleted = true;
    const tasks = [
        { id: 1, name: __("Visitor Records", "wp-statistics"), status: "Completed", records: __("5,200 records migrated", "wp-statistics") },
        { id: 2, name: __("Page Views Data", "wp-statistics"), status: "Completed", records: __("12,300 records migrated", "wp-statistics") },
        { id: 3, name: __("Geo-Location Data", "wp-statistics"), status: "In Progress", progressText: __(" (next)", "wp-statistics"), records: __("4,100 of 10,000 completed", "wp-statistics"), isBold: true },
        { id: 4, name: __("Referral Traffic", "wp-statistics"), status: "Pending", records: __("0 of 8,400 completed", "wp-statistics") },
        { id: 5, name: __("Author Performance Data", "wp-statistics"), status: "Pending", records: __("0 of 2,000 completed", "wp-statistics") },
    ];

    const getStatusDetails = (status, progressText = "") => {
        switch (status) {
            case "Completed":
                return { icon: "✅", text: __("Completed", "wp-statistics") };
            case "In Progress":
                return { icon: "🔄", text: `${__("In Progress", "wp-statistics")} ${progressText}`.trim() };
            case "Pending":
                return { icon: "⏳", text: __("Pending", "wp-statistics") };
            default:
                return { icon: "", text: status };
        }
    };

    const tableStyle = { width: "100%", borderCollapse: "collapse", overflow: "hidden", borderRadius: "8px" };
    const thStyle = { backgroundColor: "#f8f9fa", color: "#6c757d", textAlign: "left", padding: "12px 15px", fontWeight: 600, borderBottom: "1px solid #e0e0e0" };
    const tdBaseStyle = { padding: "12px 15px", borderBottom: "1px solid #eef2f7", verticalAlign: "middle", background: "white" };
    const iconStyle = { marginRight: "5px" };

    return (
        <Card style={{ width: window.innerWidth <= 768 ? "100%" : 774 }}>
            <CardBody>
                <Heading style={{ fontWeight: 500, fontSize: "18px", lineHeight: 1.3, marginTop: "8px", marginBottom: "16px" }}>{__("Overall Progress", "wp-statistics")}</Heading>
                <div>
                    <h4 style={{ fontWeight: 500, fontSize: "16px", marginBottom: "10px" }}>
                        {isCompleted ? __("100%", "wp-statistics") : __("60%", "wp-statistics")} {__("Completed", "wp-statistics")}
                    </h4>
                    <div style={{ height: "5px", borderRadius: "24px", background: "#E7E7E8", width: "100%" }}>
                        <div style={{ height: "5px", borderRadius: "24px", background: "#019939", width: isCompleted ? "100%" : "60%" }}></div>
                    </div>
                </div>

                <div style={{ border: "2px solid #eef2f7", borderRadius: "8px", margin: "24px 0px" }}>
                    <table style={tableStyle}>
                        <thead>
                            <tr>
                                <th style={thStyle}>{__("Task Name", "wp-statistics")}</th>
                                <th style={thStyle}>{__("Status", "wp-statistics")}</th>
                                <th style={thStyle}>{__("Records", "wp-statistics")}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {tasks.map((task, index) => {
                                const statusDetails = getStatusDetails(task.status, task.progressText);
                                const isLastRow = index === tasks.length - 1;
                                const tdStyle = { ...tdBaseStyle, borderBottom: isLastRow ? "none" : tdBaseStyle.borderBottom };
                                const taskStyle = { ...tdStyle, color: "#333", fontWeight: task.isBold ? 600 : "normal" };

                                return (
                                    <tr key={task.id}>
                                        <td style={taskStyle}>{task.name}</td>
                                        <td style={taskStyle}>
                                            <span style={iconStyle}>{statusDetails.icon}</span>
                                            {statusDetails.text}
                                        </td>
                                        <td style={taskStyle}>{task.records}</td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>

                {!isCompleted && (
                    <div style={{ color: "#333" }}>
                        <Heading style={{ fontSize: "16px", marginBottom: "12px" }}>{__("Status Icons Explanation:", "wp-statistics")}</Heading>
                        <ul style={{ listStyle: "disc", paddingLeft: "5px" }}>
                            <li style={{ fontSize: "15px", marginBottom: "14px", display: "flex", alignItems: "center" }}>
                                <span role="img" aria-label="Completed" style={{ marginRight: "10px", fontSize: "1.2em", color: "green" }}>
                                    ✅
                                </span>
                                <span>{__("Completed: The task is fully finished.", "wp-statistics")}</span>
                            </li>
                            <li style={{ fontSize: "15px", marginBottom: "14px", display: "flex", alignItems: "center" }}>
                                <span role="img" aria-label="In Progress" style={{ marginRight: "10px", fontSize: "1.2em" }}>
                                    🔄
                                </span>
                                <span>{__("In Progress: Task is currently migrating.", "wp-statistics")}</span>
                            </li>
                            <li style={{ fontSize: "15px", display: "flex", alignItems: "center" }}>
                                <span role="img" aria-label="Pending" style={{ marginRight: "10px", fontSize: "1.2em" }}>
                                    ⏳
                                </span>
                                <span>{__("Pending: Task is queued and will start after the current one completes.", "wp-statistics")}</span>
                            </li>
                        </ul>
                    </div>
                )}

                {isCompleted && (
                    <div style={{ maxWidth: "600px", marginTop: "40px", marginBottom: "20px", color: "#333" }}>
                        <Heading style={{ fontSize: "1.2em", fontWeight: "bold", marginBottom: "10px" }}>
                            <span style={{ marginRight: "5px" }}>🎉</span> {__("Migration Completed Successfully!", "wp-statistics")}
                        </Heading>
                        <p style={{ fontSize: "15px", color: "#0C0C0D", margin: "0px" }}>{__("All your historical data has been successfully migrated!", "wp-statistics")}</p>
                        <p style={{ fontSize: "15px", color: "#56585A", margin: "0px" }}>{__("You can now take full advantage of WP Statistics' new structure.", "wp-statistics")}</p>
                        <p style={{ fontWeight: "bold", marginBottom: "2px", marginTop: "8px", fontSize: "15px" }}>{__("Next Steps:", "wp-statistics")}</p>
                        <ul style={{ listStyleType: "disc", marginLeft: "20px", paddingLeft: "20px", color: "#555", lineHeight: 1.6, margin: 0 }}>
                            <li style={{ marginBottom: "5px", color: "#56585A", fontSize: "15px" }}>
                                {__("Check out your updated stats in the", "wp-statistics")}{" "}
                                <a href="#" style={{ color: "#0073aa", textDecoration: "underline" }}>
                                    {__("WP Statistics Dashboard", "wp-statistics")}
                                </a>
                                .
                            </li>
                            <li style={{ marginBottom: "5px", color: "#56585A", fontSize: "15px" }}>
                                {__("If you have any questions, visit our", "wp-statistics")}{" "}
                                <a href="#" style={{ color: "#0073aa", textDecoration: "underline" }}>
                                    {__("Migration FAQs", "wp-statistics")}
                                </a>{" "}
                                {__("or", "wp-statistics")}{" "}
                                <a href="#" style={{ color: "#0073aa", textDecoration: "underline" }}>
                                    {__("contact support", "wp-statistics")}
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
                                backgroundColor: "#FEF9F5",
                                border: "1px solid #E68C3F80",
                                borderRadius: "8px",
                                padding: "13px 16px",
                                color: "#333",
                                marginBottom: "24px",
                                marginTop: "12px",
                            }}
                        >
                            <div style={{ display: "flex", alignItems: "start", gap: "12px" }}>
                                <img src={InfoIcon} alt="info" />
                                <div>
                                    <div style={{ display: "flex", alignItems: "center", marginBottom: "10px" }}>
                                        <h3 style={{ margin: "0", fontSize: "14px", fontWeight: "bold", color: "#E68C3F" }}>{__("Important Notes", "wp-statistics")}</h3>
                                    </div>
                                    <ul style={{ listStyleType: "disc", paddingLeft: "15px", margin: "0" }}>
                                        <li style={{ marginBottom: "8px", fontSize: 14 }}>
                                            <strong>{__("Migration time", "wp-statistics")}</strong> {__("varies based on your data size and server resources.", "wp-statistics")}
                                        </li>
                                        <li style={{ marginBottom: "8px", fontSize: 14 }}>
                                            {__("You can continue using your site in", "wp-statistics")} <strong>{__("another browser tab", "wp-statistics")}</strong>, {__("but this migration page must remain open.", "wp-statistics")}
                                        </li>
                                        <li style={{ marginBottom: "8px", fontSize: 14 }}>
                                            {__("If the migration is", "wp-statistics")} <strong>{__("paused", "wp-statistics")}</strong> {__("or", "wp-statistics")} <strong>{__("interrupted", "wp-statistics")}</strong>, {__("returning to this page resumes it from where you left off.", "wp-statistics")}
                                        </li>
                                        <li style={{ fontSize: 14 }}>
                                            <strong>{__("No data is deleted", "wp-statistics")}</strong> {__("until the migration is fully complete—feel free to pause or cancel if you need to, without losing your old records.", "wp-statistics")}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", width: "100%", padding: "10px 0px" }}>
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
                                {__("Cancel", "wp-statistics")}
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
                                {__("Pause", "wp-statistics")}
                            </button>
                        </div>
                    </div>
                </CardFooter>
            )}
        </Card>
    );
};

export default ProgressStep;

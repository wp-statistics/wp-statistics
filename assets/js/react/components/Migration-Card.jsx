import React from "react";
import { Card, CardBody } from "@wordpress/components";

const MigrationCard = ({ name, option, onClick, children }) => {
    return (
        <Card
            style={{
                border: option === name ? "1px solid #1e87f0" : "1px solid #ccc",
                borderRadius: 8,
                padding: "24px",
                cursor: "pointer",
                boxShadow: "none",
            }}
            onClick={onClick}
        >
            <CardBody style={{ padding: "0px" }}>{children}</CardBody>
        </Card>
    );
};

export default MigrationCard;

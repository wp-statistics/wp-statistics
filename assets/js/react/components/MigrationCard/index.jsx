import React from "react";
import { Card, CardBody } from "@wordpress/components";
import classNames from "classnames";
import "../../../../scss/components/_migration-card.scss";

const MigrationCard = ({ 
    name,
    option,
    onClick, 
    children 
}) => {
    const cardClasses = classNames('migration-card', {
        'migration-card--selected': option === name
    });

    return (
        <Card
            className={cardClasses}
            onClick={onClick}
        >
            <CardBody>
                {children}
            </CardBody>
        </Card>
    );
};

export default MigrationCard; 
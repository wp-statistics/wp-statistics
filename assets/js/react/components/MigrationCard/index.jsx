import React from "react";
import {Card, CardBody} from "@wordpress/components";
import classNames from "classnames";
import styles from "./styles.module.scss";

const MigrationCard = ({name, option, onClick, children}) => {
    const cardClasses = classNames(
            styles.migrationCard,
            'wps-wrap-migrationCard',
            {
                [styles.migrationCardSelected]: option === name,
                'wps-wrap-migrationCard--selected': option === name
            }
    );

    return (
            <Card
                className={cardClasses}
                onClick={onClick}
            >
                <CardBody className={`${styles.body} wps-wrap-migrationCard__body`}>
                    {children}
                </CardBody>
            </Card>
    );
};

export default MigrationCard;
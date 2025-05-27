import { useState, useEffect, render } from "@wordpress/element";
import IntroStep from "./IntroStep";
import ConfirmationStep from "./ConfirmationStep";
import ProgressStep from "./ProgressStep";

const getStepFromURL = () => {
    const params = new URLSearchParams(window.location.search);
    return params.get("step") || "step1";
};

const DataMigration = () => {
    const [step, setStep] = useState(getStepFromURL());

    const handleStep = (item) => {
        const newURL = new URL(window.location.href);
        newURL.searchParams.set("step", item);
        window.history.pushState({}, "", newURL);
        setStep(item);
    };

    useEffect(() => {
        const handlePopState = () => {
            setStep(getStepFromURL());
        };

        window.addEventListener("popstate", handlePopState);
        return () => window.removeEventListener("popstate", handlePopState);
    }, []);

    return (
        <div
            className="wps-wrap"
            style={{
                maxWidth: window.innerWidth <= 768 ? "100%" : 774,
            }}
        >
            {step === "step1" && <IntroStep handleStep={handleStep} />}
            {step === "step2" && <ConfirmationStep handleStep={handleStep} />}
            {step === "step3" && <ProgressStep handleStep={handleStep} />}
        </div>
    );
};

// Initialize the admin page
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("wps-data-migration-page");
    if (container) {
        render(<DataMigration />, container);
    }
});

export default DataMigration; 
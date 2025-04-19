import { useState } from "@wordpress/element";
import { Card, CardBody, CardFooter, Button } from "@wordpress/components";
import { render } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import Step1 from "./step1";
import Step2 from "./step2";

const Page = () => {
    const [step, setStep] = useState("step1");
    const handleStep = (item) => {
        setStep(item);
    };
    return (
        <div
            className="wrap"
            style={{
                maxWidth: 774,
            }}
        >
            {step == "step1" && <Step1 handleStep={handleStep} />}
            {step == "step2" && <Step2 handleStep={handleStep} />}
        </div>
    );
};

// Initialize the admin page
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("wps-data-migration-page");
    if (container) {
        render(<Page />, container);
    }
});

export default Page;

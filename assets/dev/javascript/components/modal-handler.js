// Generic modals (js-openModal-<id>). The Premium popup lives in premium-modal.js.
class ModalHandler {
    constructor() {
        this.init();
    }

    init() {
        document.addEventListener('click', (event) => {
            const button = event.target.closest('[class*="js-openModal-"]');
            if (button) {
                const modalId = this.extractModalIdFromClass(button.classList);
                if (modalId) {
                    this.openModal(modalId);
                }
            }
            const actionButton = event.target.closest('button[data-action]');
            if(actionButton){
                const action = actionButton.getAttribute('data-action');
                if(action){
                    const modal = actionButton.closest('.wps-modal');
                    this.handleModalAction(modal, action);
                }
            }
        });
        this.attachOpenEvent();
        this.attachCloseEvent();
    }

    // Event delegation for opening modals
    attachOpenEvent() {
        document.addEventListener('click', (event) => {
            // Check if the clicked element or its parent matches the selector
            const button = event.target.closest('[class*="js-openModal-"]');
            if (button) {
                const modalId = this.extractModalIdFromClass(button.classList);
                if (modalId) {
                    this.openModal(modalId);
                }
            }
        });
    }

    extractModalIdFromClass(classList) {
        for (let className of classList) {
            if (className.startsWith('js-openModal-')) {
                return className.replace('js-openModal-', '').toLowerCase();
            }
        }
        return null;
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('wps-modal--open');
        } else {
            console.error(`Modal with ID "${modalId}" not found.`);
        }
    }

    // Event delegation for closing modals
    attachCloseEvent() {
        document.addEventListener('click', (event) => {
            const button = event.target.closest('.wps-modal__close');
            if (button) {
                const modal = button.closest('.wps-modal');
                if (modal) {
                    modal.classList.remove('wps-modal--open');
                }
            }
        });
    }

    handleModalAction(modal, action) {
        switch (action) {
            case 'resolve':
                break;
            case 'closeModal':
                this.closeModal(modal);
                break;
            default:
                console.warn('Unknown action:', action);
        }
    }
    closeModal(modal) {
        modal.classList.remove('wps-modal--open');
    }
}

new ModalHandler();
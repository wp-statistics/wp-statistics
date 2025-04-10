<?php
namespace WP_Statistics\Service\Admin\ModalHandler;

use WP_Statistics\Components\View;
use WP_STATISTICS\Option;

class Modal {
    const MODAL_OPTION_KEY = 'user_modals';

    /**
     * Returns the relative path to the modals directory.
     *
     * @return string
     */
    private static function getModalsDir()
    {
        return '/components/modals/';
    }

    /**
     * Shows the modal if the user has not seen it before.
     *
     * @param string $modalId The name of the modal to show.
     * @param array $args
     *
     * @return void
     */
    public static function showOnce($modalId, $args = [])
    {
        if (empty(self::getState($modalId))) {
            self::show($modalId, $args);
        }
    }

    /**
     * Shows the modal and updates the state. (stateful modal)
     *
     * @param string $modalId The name of the modal to show.
     * @param array $args
     *
     * @return void
     */
    public static function show($modalId, $args = [])
    {
        self::updateState($modalId);
        self::render($modalId, $args);
    }


    /**
     * Renders the modal with the given ID. (stateless modal)
     *
     * @param string $modalId The ID of the modal to render.
     * @param array $args
     * @param array $args
     *
     * @return void
     */
    public static function render($modalId, $args = [], $baseDir = null)
    {
        View::load(self::getModalsDir() . "/{$modalId}/{$modalId}-modal", $args, false, $baseDir);
    }

    /**
     * Updates the state of a modal.
     *
     * @param string $modalId The name of the modal.
     *
     * @return void
     */
    private static function updateState($modalId)
    {
        $modals             = self::getStates();
        $modals[$modalId]   = self::generateStateObject($modalId);

        Option::saveOptionGroup(get_current_user_id(), $modals, self::MODAL_OPTION_KEY);
    }

    /**
     * Retrieves the state of the modals.
     *
     * @return array The state of all modals.
     */
    private static function getStates()
    {
        return Option::getOptionGroup(self::MODAL_OPTION_KEY, get_current_user_id(), []);
    }

    /**
     * Retrieves the state of a modal.
     *
     * @param string $modal.
     *
     * @return array|false The state of the modal, or false if the modal has not been opened before.
     */
    private static function getState($modal)
    {
        $modals = self::getStates();
        return $modals[$modal] ?? false;
    }

    /**
     * Generates a new state object for a given modalId.
     *
     * @param string $modalId
     *
     * @return array The state object.
     */
    private static function generateStateObject($modalId)
    {
        $modal = self::getState($modalId);

        $state = [
            'times_opened'  => isset($modal['times_opened']) ? $modal['times_opened'] + 1 : 1,
            'last_opened'   => date('Y-m-d H:i:s')
        ];

        return $state;
    }
}
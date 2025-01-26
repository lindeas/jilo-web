/**
 * Messages handling system with auto-dismissing JS messages
 * Provides a consistent way to show messages across all pages
 * Use the following way in Javascript:
 *
 * - show a success message that auto-dismisses
 * JsMessages.success('All systems operational, captain');
 *
 * - show a non-dismissible error
 * JsMessages.error('Danger, Will Robinson!');
 *
 * - show a warning with custom options
 * JsMessages.warning('Custom text', true, true); // [types: success,warning,info,danger], text, [dismissible], [small]
 * JsMessages.show({
 *    type: 'info',
 *    message: 'Custom message',
 *    dismissible: true,
 *    small: false
 * });
 *
 */
const JsMessages = {
    /**
     * Show a message in the messages container
     * @param {Object} messageData - Message data object
     * @param {string} messageData.type - Message type (success, danger, warning, info)
     * @param {string} messageData.message - Message text
     * @param {boolean} messageData.dismissible - Whether the message can be dismissed
     * @param {boolean} messageData.small - Whether to use small styling
     */
    show: function(messageData) {
        const dismissClass = messageData.dismissible ? ' alert-dismissible fade' : '';
        const dismissButton = messageData.dismissible ? 
            `<button type="button" class="btn-close${messageData.small ? ' btn-close-sm' : ''}" data-bs-dismiss="alert" aria-label="Close"></button>` : '';
        const smallClass = messageData.small ? ' alert-sm' : '';

        const $alert = $('<div>')
            .addClass(`alert alert-${messageData.type}${dismissClass}${smallClass}`)
            .attr('role', 'alert')
            .html(`${messageData.message}${dismissButton}`);

        // Remove any existing alerts
        $('#messages-container').empty().append($alert);

        // Trigger reflow to ensure transition works
        $alert[0].offsetHeight;

        // Show the alert with transition
        $alert.addClass('show');

        if (messageData.dismissible) {
            setTimeout(() => {
                $alert.removeClass('show');
                setTimeout(() => {
                    $alert.remove();
                }, 200); // Same as transition duration
            }, 1500);
        }
    },

    /**
     * Show a success message
     * @param {string} message - Message text
     * @param {boolean} dismissible - Whether the message can be dismissed
     * @param {boolean} small - Whether to use small styling
     */
    success: function(message, dismissible = true, small = false) {
        this.show({
            type: 'success',
            message: message,
            dismissible: dismissible,
            small: small
        });
    },

    /**
     * Show an error message
     * @param {string} message - Message text
     * @param {boolean} dismissible - Whether the message can be dismissed
     * @param {boolean} small - Whether to use small styling
     */
    error: function(message, dismissible = false, small = false) {
        this.show({
            type: 'danger',
            message: message,
            dismissible: dismissible,
            small: small
        });
    },

    /**
     * Show a warning message
     * @param {string} message - Message text
     * @param {boolean} dismissible - Whether the message can be dismissed
     * @param {boolean} small - Whether to use small styling
     */
    warning: function(message, dismissible = true, small = false) {
        this.show({
            type: 'warning',
            message: message,
            dismissible: dismissible,
            small: small
        });
    },

    /**
     * Show an info message
     * @param {string} message - Message text
     * @param {boolean} dismissible - Whether the message can be dismissed
     * @param {boolean} small - Whether to use small styling
     */
    info: function(message, dismissible = true, small = false) {
        this.show({
            type: 'info',
            message: message,
            dismissible: dismissible,
            small: small
        });
    }
};

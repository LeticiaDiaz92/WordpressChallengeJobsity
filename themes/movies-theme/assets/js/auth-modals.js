/**
 * Authentication Modals functionality
 */
(function($) {
    'use strict';

    class AuthModals {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            // Modal triggers
            $(document).on('click', '.login-modal-trigger', this.openLoginModal.bind(this));
            $(document).on('click', '.register-modal-trigger', this.openRegisterModal.bind(this));
            
            // Modal close
            $(document).on('click', '.modal-close, .modal-overlay', this.closeModals.bind(this));
            
            // Switch between login/register
            $(document).on('click', '.switch-to-register', this.switchToRegister.bind(this));
            $(document).on('click', '.switch-to-login', this.switchToLogin.bind(this));
            
            // Escape key close
            $(document).on('keydown', this.handleEscapeKey.bind(this));
            
            // Prevent modal content click from closing modal
            $(document).on('click', '.modal-content', function(e) {
                e.stopPropagation();
            });
        }

        openLoginModal(e) {
            e.preventDefault();
            this.closeModals();
            this.showModal('#login-modal');
        }

        openRegisterModal(e) {
            e.preventDefault();
            this.closeModals();
            this.showModal('#register-modal');
        }

        switchToRegister(e) {
            e.preventDefault();
            this.closeModals();
            this.showModal('#register-modal');
        }

        switchToLogin(e) {
            e.preventDefault();
            this.closeModals();
            this.showModal('#login-modal');
        }

        showModal(modalId) {
            const $modal = $(modalId);
            $modal.addClass('active');
            $('body').addClass('modal-open');
            
            // Focus first input
            setTimeout(() => {
                $modal.find('input:first').focus();
            }, 300);
        }

        closeModals() {
            $('.auth-modal').removeClass('active');
            $('body').removeClass('modal-open');
        }

        handleEscapeKey(e) {
            if (e.key === 'Escape') {
                this.closeModals();
            }
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        new AuthModals();
    });

})(jQuery); 
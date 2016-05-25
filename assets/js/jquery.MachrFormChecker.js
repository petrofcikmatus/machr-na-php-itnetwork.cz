(function (window, document, $, undefined) {

    var MachrFormChecker = {

        $container: null,

        objects: {
            emails: [],
            passwords: [],
            keys: []
        },

        init: function ($container) {
            this.$container = $container;
            this.loadObjects();
            this.attachHandlers();
        },

        //---------------------------------------------------------------------
        
        submit: function () {
            this.removeMessages();
            return this.isFormValid();
        },

        attachHandlers: function () {
            this.$container.on('submit', function (event) {
                if (!MachrFormChecker.submit()) {
                    event.preventDefault();
                }
            });
        },

        loadObjects: function () {
            this.objects.emails = this.$container.find('input[id^=inputEmail]');
            this.objects.passwords = this.$container.find('input[id^=inputPassword]');
            this.objects.keys = this.$container.find('input[id^=inputKey]');
        },

        isFormValid: function () {
            var thisFormHandler = this;
            var formIsValid = true;

            this.objects.emails.each(function () {
                var object = $(this);
                var value = object.val();

                if (!thisFormHandler.isNotEmpty(value)) {
                    thisFormHandler.addMessage(object, 'Zadaj email.');
                    formIsValid = false;
                } else if (!thisFormHandler.isValidEmail(value)) {
                    thisFormHandler.addMessage(object, 'Zadaj email v správnom formáte.');
                    formIsValid = false;
                }
            });

            this.objects.passwords.each(function () {
                var object = $(this);
                var value = object.val();

                if (!thisFormHandler.isNotEmpty(value)) {
                    thisFormHandler.addMessage(object, 'Zadaj heslo.');
                    formIsValid = false;
                } else if (!thisFormHandler.isValidPassword(value)) {
                    thisFormHandler.addMessage(object, 'Zadaj heslo s minimálne 6 znakmi.');
                    formIsValid = false;
                }
            });

            this.objects.keys.each(function () {
                var object = $(this);
                var value = object.val();

                if (!thisFormHandler.isNotEmpty(value)) {
                    thisFormHandler.addMessage(object, 'Zadaj kľúč.');
                    formIsValid = false;
                }
            });

            return formIsValid;
        },

        addMessage: function (object, message) {
            object.after(this.getMessageHTML(message));
            object.parents('.form-group').addClass('has-error');
        },

        removeMessages: function () {
            this.$container.find('.help-block').remove();
            this.$container.find('.form-group').removeClass('has-error');
        },

        getMessageHTML: function (message) {
            return '<span class="help-block">' + message + '</span>';
        },

        isNotEmpty: function (string) {
            return (typeof(string) == 'string' && string.length > 0);
        },

        isValidPassword: function (string) {
            return (typeof (string) == 'string' && string.length > 5);
        },

        isValidEmail: function (string) {
            var regex = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
            return regex.test(string);
        }
    };

    $.fn.machrFormChecker = function () {
        return this.each(function () {
            MachrFormChecker.init($(this));
        });
    };
})(window, document, jQuery);
var RequestAccessModalHelper = function () {
    var self = this;

    this.openRequestAccessModal = function (html) {
        $('#request-access').html(html);
        $(document.body).css({ overflowY: 'hidden'});
        $('#request-access-scroller').show().data('opened', true);

        $('.close-modal').on('click', function closeModal(e) {
            e.preventDefault();
            self.closeRequestAccessModal();
        });
        $('#name').focus();

        $('#request_access_submit').on('click', function (e) {
            e.preventDefault();
            var formData = $('#request_access_form').serialize();
            $.post('/authentication/idp/performRequestAccess', formData)
                .done(function (data) {
                    self.openRequestAccessModal(data);
                });
        });
    };

    this.closeRequestAccessModal = function () {
        $('#request-access-scroller').hide();
        $(document.body).css({ overflowY: 'auto'});
        $('#request-access').html('');
        $('input.mod-search-input').focus();
    }
};

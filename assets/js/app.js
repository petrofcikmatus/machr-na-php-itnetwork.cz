$(document).ready(function () {
    $('form').machrFormChecker();

    $('.js-for-sure').on('click', function () {
        return confirm('Určite?');
    })
});
// buggy if a selection is made prior to selecting all
$('#chkbox-select-all').on('click', function() {
    $("input[id^='chkbox-employee-']").attr("checked", $(this).is(':checked'));
});
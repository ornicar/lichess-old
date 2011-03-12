$(function() {
    var $editable = $editable = $('#lichess_user div.editable').orNot();
    if ($editable) {
        $editable.editableSet({
            event: "click",
            action: $editable.attr('data-url'),
            dataType: 'json'
        });
    }
});

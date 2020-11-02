$(document).off('change', ".nid1");
$(document).on('change', ".nid1", function () {
    var t= $(".nid1").val();
    $.get('NovelSelect',{q:t},function(data){
        $("#title1").val(data);
    })
});


$(document).off('change', ".nid2");
$(document).on('change', ".nid2", function () {
    var t= $(".nid2").val();
    $.get('NovelSelect',{q:t},function(data){
        $("#title2").val(data);
    })
});
$(document).off('change', ".nid3");
$(document).on('change', ".nid3", function () {
    var t= $(".nid3").val();
    $.get('NovelSelect',{q:t},function(data){
        $("#title3").val(data);
    })
});


$(document).off('change', ".nid4");
$(document).on('change', ".nid4", function () {
    var t= $(".nid4").val();
    $.get('NovelSelect',{q:t},function(data){
        $("#title4").val(data);
    })
});

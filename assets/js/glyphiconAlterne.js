$(".glyphicon").each(function () {
    $(this).parent().on("click", function () {
        if ($(this).find(".glyphicon").attr("class") =="glyphicon glyphicon-chevron-down") {
            $(this).find(".glyphicon").attr("class", "glyphicon glyphicon-chevron-up")
        } else {
            $(this).find(".glyphicon").attr("class", "glyphicon glyphicon-chevron-down")
        }
    })
})
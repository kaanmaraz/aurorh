import $ from 'jquery';
// create global $ and jQuery variables
global.$ = global.jQuery = $;
import "select2/dist/css/select2.min.css"
import "select2/dist/js/select2.js"
import "../styles/select2.css"
$(".select2.multiple").select2({
    width: "100%",
    multiple: true
})

$("select:not(.multiple)").select2({
    width: "100%",
    multiple: false
})
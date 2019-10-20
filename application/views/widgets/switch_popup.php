<li class="dropdown" style="padding: 10px 8px 0 5px">
    <label class="switch switch-primary" title="@Turn on, off show popup when call@" data-toggle="tooltip" data-placement="bottom">
        <input id="callPopupSwitch" type="checkbox" onchange="switchPopup(this)" checked><span></span>
    </label>
</li>
<script type="text/javascript">
function switchPopup(ele) {
    sessionStorage.setItem('callPopup', ele.checked);
    notification.show(`Turn ${ele.checked ? 'on' : 'off'} popup when call.`, "info");
}

var currentUrl = () => getUrlParams(window.location.href);
if(currentUrl.callPopup) {
    sessionStorage.setItem('callPopup', false);
}

if(sessionStorage.getItem('callPopup') == null) {
    // Turn on Popup when first load
    sessionStorage.setItem('callPopup', true);
} else {
    $("#callPopupSwitch").prop("checked", sessionStorage.getItem('callPopup') == 'true');
}
</script>
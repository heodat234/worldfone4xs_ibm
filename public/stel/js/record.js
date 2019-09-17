function templateRecord(data) {
    if(data.disposition && data.disposition=="ANSWERED" && data.billduration && data.endtime && (Date.now()-data.endtime*1000 )>30000 ){
        return `<button class="btn btn-sm btn-default btn-play" onclick="play('${data.calluuid}')"><i class="fa fa-play"></i></button>
        <button onclick="downloadRecord('${data.calluuid}')" class="btn btn-sm btn-default" data-toggle="tooltip" title="" data-original-title="Download"><i class="fa fa-cloud-download"></i></button>`;
    } else {
        return "";
    }
}

var playNotification = $("#play-notification").kendoNotification({
    hideOnClick: true,
    autoHideAfter: 0,
    position: {
        left: 220,
        bottom: 20
    },
    show: function (e) {
        e.element.parent().css({
          zIndex: 11000
        });
    }
}).data("kendoNotification");

function play(calluuid, autoplay = true) {
    var url = `${ENV.baseUrl}playback/record/play?calluuid=${calluuid}`
    playNotification.show(`<audio controls ${autoplay ? "autoplay" : ""}}>
          <source src="${url}" type="audio/mpeg">
        Your browser does not support the audio element.
        </audio>`, "success");
}

function downloadRecord(calluuid) {
    return window.location.href=`${ENV.baseUrl}playback/record/download?calluuid=${calluuid}`;
}

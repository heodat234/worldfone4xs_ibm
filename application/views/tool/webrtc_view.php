<div class="videos">
    <video id="localVideo" autoplay></video>
    <video id="remoteVideo" autoplay class="hidden"></video>
</div>

<script type="text/javascript">
    var WS_ADDRESS = '<?php echo $webSocketURL;?>';

    var cid = getUrlParam('cid');
    if (cid == '' || cid == null) {
        cid = Math.random().toString(36).substr(2);
        location.href = ENV.currentUri + '?cid=' + cid;
    }
    var answer = 0;

    var subject = 'private-video-room-'+cid;

    var ws = new WebSocket(WS_ADDRESS);
    ws.onopen = function(){
        subscribe(subject);
        navigator.mediaDevices.getUserMedia({
            audio: true,
            video: true
        }).then(function (stream) {
            localVideo.srcObject = stream;
            localStream = stream;
            localVideo.addEventListener('loadedmetadata', function () {
                publish('client-call', null)
            });
        }).catch(function (e) {
            alert(e);
        });
    };
    ws.onmessage = function(e){
        var package = JSON.parse(e.data);
        var data = package.data;
        switch (package.event) {
            case 'client-call':
                icecandidate(localStream);
                pc.createOffer({
                    offerToReceiveAudio: 1,
                    offerToReceiveVideo: 1
                }).then(function (desc) {
                    pc.setLocalDescription(desc).then(
                        function () {
                            publish('client-offer', pc.localDescription);
                        }
                    ).catch(function (e) {
                        alert(e);
                    });
                }).catch(function (e) {
                    alert(e);
                });
                break;
            case 'client-answer':
                pc.setRemoteDescription(new RTCSessionDescription(data),function(){}, function(e){
                    alert(e);
                });
                break;
            case 'client-offer':
                icecandidate(localStream);
                pc.setRemoteDescription(new RTCSessionDescription(data), function(){
                    if (!answer) {
                        pc.createAnswer(function (desc) {
                                pc.setLocalDescription(desc, function () {
                                    publish('client-answer', pc.localDescription);
                                }, function(e){
                                    alert(e);
                                });
                            }
                        ,function(e){
                            alert(e);
                        });
                        answer = 1;
                    }
                }, function(e){
                    alert(e);
                });
                break;
            case 'client-candidate':
                pc.addIceCandidate(new RTCIceCandidate(data), function(){}, function(e){alert(e);});
                break;
        }
    };

    var localVideo = document.getElementById('localVideo');
    var remoteVideo = document.getElementById('remoteVideo');

    navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
    var configuration = {
        iceServers: [{
            urls: 'stun:stun.xten.com'
        }]
    };
    var pc, localStream;

    function icecandidate(localStream) {
        pc = new RTCPeerConnection(configuration);
        pc.onicecandidate = function (event) {
            if (event.candidate) {
                publish('client-candidate', event.candidate);
            }
        };
        try{
            pc.addStream(localStream);
        }catch(e){
            var tracks = localStream.getTracks();
            for(var i=0;i<tracks.length;i++){
                pc.addTrack(tracks[i], localStream);
            }
        }
        pc.onaddstream = function (e) {
            $('#remoteVideo').removeClass('hidden');
            $('#localVideo').remove();
            remoteVideo.srcObject = e.stream;
        };
    }

    function publish(event, data) {
        ws.send(JSON.stringify({
            cmd:'publish',
            subject: subject,
            event:event,
            data:data
        }));
    }

    function subscribe(subject) {
        ws.send(JSON.stringify({
            cmd:'subscribe',
            subject:subject
        }));
    }

    function getUrlParam(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]);
        return null;
    }
</script>